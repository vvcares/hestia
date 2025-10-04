#!/bin/bash
# VV_IPBan - UFW+Fail2Ban+IPSet with Email Notifications (Minimal with Indicators)

# --- Configuration ---
IPSET_NAME="VV_BLOCK"
TEMP_FILE="/tmp/emerging_temp.txt"
F2B_ACTION_FILE="/etc/fail2ban/action.d/vv-ipset.conf"
EXT_BLOCKLIST="https://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt"
NOTIFICATION_EMAIL="sales@vvcares.com" # <<-- REPLACE
IPSET_CMD="/sbin/ipset"; IPTABLES_CMD="/sbin/iptables"; UFW_CMD="/usr/sbin/ufw"; MAIL_CMD="/usr/bin/mail"
HOSTNAME=$(hostname); GREEN='\033[0;32m'; RED='\033[0;31m'; YELLOW='\033[0;33m'; NC='\033[0m'
HESTIA_INSTALLED=0

# 1. Notification Function (Updated for Symbols and ASCII removal)
send_notification() {
    local status="$1" body="$2"
    local indicator="❌"
    local subject="FAILURE: VV_IPBan Error on $HOSTNAME"
    
    # --- MODIFICATION: Strip ASCII color codes from the body (removes all sequences starting with \033[)
    local clean_body
    clean_body=$(echo -e "$body" | sed -E 's/\x1b\[[0-9;]*m//g')
    # --- END MODIFICATION ---

    if [[ "$status" == "SUCCESS" ]]; then
        indicator="✅"
        subject=" VV_IPBan Update on $HOSTNAME"
    fi
    subject="$indicator $subject"

    if command -v "$MAIL_CMD" &> /dev/null; then
        # Use the clean_body variable for the email content
        echo -e "$clean_body" | "$MAIL_CMD" -s "$subject" "$NOTIFICATION_EMAIL"
    else
        echo "WARNING: Mail command ($MAIL_CMD) not found. Cannot send notification."
    fi
}

# --- Log Setup ---
LOG_OUTPUT=""
# capture_log still includes the color codes for terminal display, but they are stripped before emailing.
capture_log() { echo -e "$@"; LOG_OUTPUT+="$@\n"; }
trap 'rm -f "$TEMP_FILE"' EXIT
exec 3>&1; exec 1> >(while IFS= read -r line; do capture_log "$line"; done)

# 2. Dependency & Root Check
[[ $EUID -ne 0 ]] && { capture_log "${RED}ERROR: Must be run as root.${NC}"; exit 1; }
for TOOL in ipset iptables fail2ban-client wget systemctl; do
    command -v "$TOOL" &> /dev/null || { capture_log "${RED}FATAL ERROR: Tool $TOOL MISSING.${NC}"; exit 1; }
done

# 3. HestiaCP Detection and Conditional UFW Check
capture_log "Checking service status..."
if systemctl status hestia &> /dev/null; then
    HESTIA_INSTALLED=1
    capture_log "${YELLOW}INFO: HestiaCP detected. UFW status check skipped.${NC}"
else
    command -v "$UFW_CMD" &> /dev/null || { capture_log "${RED}FATAL ERROR: Tool ufw MISSING.${NC}"; exit 1; }
    if ! "$UFW_CMD" status | grep -q 'Status: active'; then
        BODY="UFW Firewall is **Inactive**. Action: run 'sudo ufw enable'.\n\n$LOG_OUTPUT"
        exec 1>&3 3>&-
        send_notification "ERROR" "$BODY"
        echo -e "UFW: ${RED}Inactive. See email.${NC}"; exit 1
    fi
    capture_log "UFW Firewall: ${GREEN}Active${NC}"
fi

# 4. Initial IPSet/IPTables Setup
capture_log "Ensuring IPSet and IPTables rules are configured..."
$IPSET_CMD list -n | grep -q "$IPSET_NAME" || $IPSET_CMD create "$IPSET_NAME" hash:net family inet maxelem 65536

# --- MODIFIED LINES (UFW-ET-BLOCK renamed to VV_BLOCK) ---
if ! $IPTABLES_CMD -L VV_BLOCK -n &> /dev/null; then
    $IPTABLES_CMD -N VV_BLOCK
    $IPTABLES_CMD -I ufw-before-input 1 -j VV_BLOCK 2> /dev/null || $IPTABLES_CMD -I INPUT 1 -j VV_BLOCK
    $IPTABLES_CMD -A VV_BLOCK -m set --match-set "$IPSET_NAME" src -j REJECT --reject-with icmp-port-unreachable
# --- END MODIFIED LINES ---

    if [ "$HESTIA_INSTALLED" -eq 0 ] && command -v "$UFW_CMD" &> /dev/null; then
        $UFW_CMD reload; capture_log "UFW/IPTables integration rules created and reloaded."
    else
        capture_log "IPTables integration rules created (no UFW reload performed)."
    fi
fi

# 5. Fail2Ban Action Setup
capture_log "Setting up Fail2Ban IPSet action..."
cat <<EOF > "$F2B_ACTION_FILE"
[Definition]
actionban = $IPSET_CMD add $IPSET_NAME <ip> timeout <bantime>
actionunban = $IPSET_CMD del $IPSET_NAME <ip>
EOF
systemctl reload fail2ban 2> /dev/null || fail2ban-client reload 2> /dev/null

# 6. Download, Filter, and Prepare List
capture_log "Downloading and preparing external blocklist..."
if ! wget -q -O - -U "Mozilla/5.0" "$EXT_BLOCKLIST" | grep -v '^;' | tr -d '\r' | \
awk -v ipset_name="$IPSET_NAME" '$1 ~ /^[0-9.]+(\/[0-9]{1,2})?$/ { print "add " ipset_name " " $1 " -exist" }' > "$TEMP_FILE"; then
    BODY="Failed to download/process blocklist from $EXT_BLOCKLIST.\n\n$LOG_OUTPUT"
    exec 1>&3 3>&-
    send_notification "ERROR" "$BODY"
    echo -e "${RED}ERROR: Download/process failed. See email.${NC}"; exit 1
fi

# 7. Direct IPSet Reload
capture_log "Flushing and restoring IPSet: $IPSET_NAME"
$IPSET_CMD flush "$IPSET_NAME" || {
    BODY="Failed to FLUSH IPSet: $IPSET_NAME.\n\n$LOG_OUTPUT"
    exec 1>&3 3>&-
    send_notification "ERROR" "$BODY"
    echo -e "${RED}ERROR: Failed to flush IPSet. See email.${NC}"; exit 1;
}

if [ -s "$TEMP_FILE" ]; then
    $IPSET_CMD restore -exist < "$TEMP_FILE" || {
        BODY="Failed to RESTORE IPSet. Set is now **EMPTY**.\n\n$LOG_OUTPUT"
        exec 1>&3 3>&-
        send_notification "ERROR" "$BODY"
        echo -e "${RED}CRITICAL ERROR: Restore failed. Set is EMPTY. See email.${NC}"; exit 1;
    }
else
    capture_log "${RED}WARNING: Downloaded list was empty. IPSet flushed but not restored.${NC}"
fi

# 8. Finalize and Verify
if [ "$HESTIA_INSTALLED" -eq 0 ] && command -v "$UFW_CMD" &> /dev/null; then
    $UFW_CMD reload
fi
FINAL_COUNT=$($IPSET_CMD list "$IPSET_NAME" | grep "Number of entries")

capture_log "${GREEN}Update complete.${NC}"
capture_log "$FINAL_COUNT"

# Restore stdout and Send Success Notification
exec 1>&3 3>&-
BODY="VV_IPBan update finished successfully.\n\n$FINAL_COUNT\n\n--- Script Log ---\n$LOG_OUTPUT"
send_notification "SUCCESS" "$BODY"
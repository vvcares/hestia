<?php
error_reporting(NULL);
$TAB = ' CSF';

// =========================================================================
// HESTIA CP INTEGRATION & HEADER INCLUDES
// =========================================================================
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

include($_SERVER['DOCUMENT_ROOT'].'/templates/header.php');

render_page($user, $TAB, "");

?>

<div class="toolbar"></div>

<style>
    /* ------------------------------------------------------------------- */
    /* FINAL STYLING: HORIZONTAL MAIN NAV WITH FULL-WIDTH SUB-MENU BAR */
    /* ------------------------------------------------------------------- */
    .csf-container {
        max-width: 1200px; 
        margin: 20px auto;
        padding: 0 15px;
    }
    .csf-main-header {
        text-align: center;
        font-size: 2.2em;
        font-weight: 200;
        margin: 20px 0 30px 0;
        color: #fff;
    }
    
    /* 1. Main Horizontal Navigation Bar */
    .csf-main-nav-wrapper {
        display: flex; /* Use Flexbox for clean horizontal buttons */
        gap: 10px;
        margin-bottom: 0; /* Remove space between main nav and sub-nav */
    }
    /* Main Buttons (Simulated Details elements) */
    .csf-nav-button {
        flex: 1; /* Make each button take equal horizontal space */
        cursor: pointer;
        color: white;
        padding: 15px;
        font-size: 1.1em;
        font-weight: 500;
        border-radius: 6px;
        text-align: center;
        transition: background-color 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .csf-nav-button i {
        margin-right: 10px;
    }
    /* Active State for Main Button */
    .csf-nav-button.active {
        /* Change color slightly and lift it up visually */
        background-color: #303f9f !important; 
        box-shadow: 0 0 0 rgba(0, 0, 0, 0); 
        border-radius: 6px 6px 0 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: none; /* Make it look connected to the sub-menu */
        margin-bottom: -1px; /* Overlap border */
    }

    /* 2. Full-Width Sub-Navigation Content Area */
    .csf-sub-nav-content {
        background-color: #21252b; 
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0 0 6px 6px; 
        padding: 15px;
        margin-top: 0; 
        display: none; /* Hidden by default */
    }
    .csf-sub-nav-content.active {
        display: block; /* Show active sub-menu */
    }

    /* Sub-Links Grid Layout (Horizontal Rows) */
    .csf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }
    /* Special grid for Quick Actions (2 columns max) */
    #content-quick-actions .csf-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .csf-grid-item a {
        display: flex;
        align-items: center;
        justify-content: center; 
        width: 100%;
        padding: 12px 10px;
        text-decoration: none;
        color: white !important;
        font-size: 0.9em;
        font-weight: 500;
        border-radius: 4px;
        transition: all 0.2s ease;
        box-shadow: none;
    }
    .csf-grid-item i {
        margin-right: 8px;
        font-size: 1em;
    }
    .csf-grid-item a:hover {
        opacity: 0.9;
    }
    
    /* Color Palette */
    .csf-header { background-color: #3f51b5; } 
    .csf-status { background-color: #ce0d0d; } 
    .csf-config-btn { background-color: #d48004; } 
    .csf-lfd-btn { background-color: #673ab7; } 
    .csf-log-btn { background-color: #03a9f4; } 

    /* Summary Button Colors */
    .summary-status { background-color: #3f51b5; } 
    .summary-config { background-color: #d48004; } 
    .summary-lfd { background-color: #673ab7; } 
    .summary-log { background-color: #03a9f4; } 

    /* Media query for smaller screens: stack main navigation vertically */
    @media (max-width: 992px) {
        .csf-main-nav-wrapper {
            /* Fall back to 2 columns, then 1 column */
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
        }
    }
    @media (max-width: 576px) {
        .csf-main-nav-wrapper {
            grid-template-columns: 1fr; /* Stack vertically on very small screens */
        }
        .csf-nav-button.active {
            border-radius: 6px 6px 0 0;
        }
    }
</style>

<script>
/**
 * Handles showing the correct sub-menu and loading content.
 * @param {string} queryString - The value of the 'action' parameter, or a full query string.
 * @param {HTMLElement} element - The button element that was clicked (can be null for initial load).
 * @param {string} targetContentId - The ID of the sub-nav content panel to show (e.g., 'content-config').
 */
function loadContent(queryString, element, targetContentId) {
    // 1. Handle Content Loading
    const hiddenForm = document.getElementById('csf_submit_form');
    const actionInput = document.getElementById('csf_action_input');
    
    if (queryString) {
        if (queryString.includes('=')) {
            hiddenForm.action = '/list/csf/frame.php?' + queryString;
            actionInput.name = ''; 
        } else {
            hiddenForm.action = '/list/csf/frame.php';
            actionInput.name = 'action'; 
            actionInput.value = queryString; 
        }
        hiddenForm.target = 'myiframe';
        hiddenForm.submit();
    }
    
    // 2. Handle Tab/Sub-Menu Switching (Only if a main button was clicked)
    if (targetContentId) {
        // Remove 'active' from all main buttons and content panels
        document.querySelectorAll('.csf-nav-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.csf-sub-nav-content').forEach(content => content.classList.remove('active'));
        
        // Add 'active' to the clicked button and its corresponding content panel
        if (element) {
            element.classList.add('active');
        }
        const targetContent = document.getElementById(targetContentId);
        if (targetContent) {
            targetContent.classList.add('active');
        }
    }

    return false; // Prevent anchor link's default behavior
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial Load: Load default content and activate default button/tab
    const defaultButton = document.getElementById('btn-quick-actions');
    const defaultAction = ''; // Default action for UPGRADE / STATUS INFO
    
    // Set initial state
    defaultButton.classList.add('active');
    document.getElementById('content-quick-actions').classList.add('active');
    
    // Load default iframe content
    loadContent(defaultAction, null, null); 
});
</script>


<form id="csf_submit_form" method="get" style="display:none;">
    <input type="hidden" name="action" id="csf_action_input" value="">
</form>


<div class="csf-container">
    <div class="csf-main-header"><i class="fas fa-shield-alt"></i> ConfigServer Security & Firewall</div>

    <div class="csf-main-nav-wrapper">
        
        <div id="btn-quick-actions" class="csf-nav-button summary-status" 
             onclick="loadContent(null, this, 'content-quick-actions');">
            <i class="fas fa-bolt"></i> Quick Actions & Status
        </div>
	

        <div id="btn-config" class="csf-nav-button summary-config" 
             onclick="loadContent(null, this, 'content-config');">
            <i class="fas fa-cog"></i> CSF Configuration & Lists
        </div>

        <div id="btn-lfd" class="csf-nav-button summary-lfd" 
             onclick="loadContent(null, this, 'content-lfd');">
            <i class="fas fa-heartbeat"></i> Login Failure Daemon (LFD)
        </div>
        
        <div id="btn-log" class="csf-nav-button summary-log" 
             onclick="loadContent(null, this, 'content-log');">
            <i class="fas fa-search"></i> Diagnostics & Logs
        </div>

    </div> <div class="csf-sub-nav-content" id="content-quick-actions">
        <div class="csf-grid">
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('restart', this, null);" class="csf-button csf-status"><i class="fas fa-sync-alt"></i> RESTART CSF & LFD</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('manualcheck', this, null);" class="csf-button csf-header"><i class="fas fa-info-circle"></i> UPGRADE / STATUS INFO</a>
            </div>
			<div class="csf-grid-item">
                <a href="#" onclick="return loadContent('#', this, null);" class="csf-button csf-header"><i class="fas fa-info-circle"></i> ALL SETTINGS</a>
            </div>
        </div>
    </div>

    <div class="csf-sub-nav-content" id="content-config">
        <div class="csf-grid">
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('conf', this, null);" class="csf-button csf-config-btn"><i class="fas fa-file-alt"></i> Main Configuration</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('allow', this, null);" class="csf-button csf-config-btn"><i class="fas fa-user-check"></i> Firewall Allow IP's</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('deny', this, null);" class="csf-button csf-config-btn"><i class="fas fa-ban"></i> Firewall Deny IP's</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('temp', this, null);" class="csf-button csf-config-btn"><i class="fas fa-clock"></i> Temporary Entries</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('sips', this, null);" class="csf-button csf-config-btn"><i class="fas fa-server"></i> Deny Server IP's</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('profiles', this, null);" class="csf-button csf-config-btn"><i class="fas fa-users-cog"></i> Firewall Profiles</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('status', this, null);" class="csf-button csf-config-btn"><i class="fas fa-list-ol"></i> View Iptables Rules</a>
            </div>
        </div>
    </div>

    <div class="csf-sub-nav-content" id="content-lfd">
        <div class="csf-grid">
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('lfdstatus', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-check-circle"></i> LFD Status</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('lfdrestart', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-redo"></i> Restart LFD Only</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('lfd', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-file-code"></i> Edit LFD Ignore Files</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('dyndns', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-globe"></i> LFD Dynamic DNS</a>
            </div>
        </div>
    </div>
    
    <div class="csf-sub-nav-content" id="content-log">
        <div class="csf-grid">
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('servercheck', this, null);" class="csf-button csf-log-btn"><i class="fas fa-shield-alt"></i> Check Server Security</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('logtail', this, null);" class="csf-button csf-log-btn"><i class="fas fa-terminal"></i> Watch System Logs</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('loggrep', this, null);" class="csf-button csf-log-btn"><i class="fas fa-search-plus"></i> Search System Logs</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('viewports', this, null);" class="csf-button csf-log-btn"><i class="fas fa-network-wired"></i> View Listening Ports</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('viewlogs', this, null);" class="csf-button csf-log-btn"><i class="fas fa-file-alt"></i> View Iptables Log</a>
            </div>
            <div class="csf-grid-item">
                <a href="#" onclick="return loadContent('csftest', this, null);" class="csf-button csf-log-btn"><i class="fas fa-vial"></i> Test Iptables</a>
            </div>
        </div>
    </div>


</div>
    <div class="l-center units" style="margin:1em;text-align: center;">

<iframe  scrolling='auto' name='myiframe' id='myiframe' frameborder='0' width='90%' onload='resizeIframe(this);'></iframe>
<script>
function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
</script>
</div>
<?php

include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.php');

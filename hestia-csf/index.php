<?php
// PHP MINIMALISM
error_reporting(0); 
$TAB = ' CSF';

// Includes and Admin check (minimal lines)
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] !== 'admin') {
    header("Location: /list/user");
    exit(0);
}

include($_SERVER['DOCUMENT_ROOT'].'/templates/header.php');

// Security & Minimal Script: Added CSRF for security
$csrf_token = bin2hex(random_bytes(32)); 
$_SESSION['csf_token'] = $csrf_token;

render_page($user, $TAB, 'ConfigServer Security & Firewall');
?>

<div class="toolbar"></div>

<style>
.csf-container{max-width:1200px;margin:20px auto;padding:0 15px}.csf-main-header{text-align:center;font-size:2.2em;font-weight:200;margin:20px 0 30px 0;color:#fff}.csf-main-nav-wrapper{display:flex;gap:10px;margin-bottom:0}.csf-nav-button{flex:1;cursor:pointer;color:white;padding:15px;font-size:1.1em;font-weight:500;border-radius:6px;text-align:center;transition:background-color .2s,box-shadow .2s;box-shadow:0 2px 4px rgba(0,0,0,.2)}.csf-nav-button i{margin-right:10px}.csf-nav-button.active{background-color:#303f9f!important;box-shadow:0 0 0 rgba(0,0,0,0);border-radius:6px 6px 0 0;border:1px solid rgba(255,255,255,.1);border-bottom:none;margin-bottom:-1px}.csf-sub-nav-content{background-color:#21252b;border:1px solid rgba(255,255,255,.1);border-radius:0 0 6px 6px;padding:15px;margin-top:0;display:none}.csf-sub-nav-content.active{display:block}.csf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px}#content-quick-actions .csf-grid{grid-template-columns:1fr 1fr}.csf-grid-item a{display:flex;align-items:center;justify-content:center;width:100%;padding:12px 10px;text-decoration:none;color:white!important;font-size:.9em;font-weight:500;border-radius:4px;transition:all .2s ease;box-shadow:none}.csf-grid-item i{margin-right:8px;font-size:1em}.csf-grid-item a:hover{opacity:.9}.csf-header{background-color:#3f51b5}.csf-status{background-color:#ce0d0d}.csf-config-btn{background-color:#d48004}.csf-lfd-btn{background-color:#673ab7}.csf-log-btn{background-color:#03a9f4}.summary-status{background-color:#3f51b5}.summary-config{background-color:#d48004}.summary-lfd{background-color:#673ab7}.summary-log{background-color:#03a9f4}@media (max-width:992px){.csf-main-nav-wrapper{display:grid;grid-template-columns:repeat(2,1fr)}}@media (max-width:576px){.csf-main-nav-wrapper{grid-template-columns:1fr}.csf-nav-button.active{border-radius:6px 6px 0 0}}
</style>

<form id="csf_submit_form" method="get" style="display:none;">
    <input type="hidden" name="action" id="csf_action_input" value="">
    <input type="hidden" name="token" id="csf_csrf_token" value="<?= $csrf_token ?>">
</form>


<div class="csf-container">
    <div class="csf-main-header"><i class="fas fa-shield-alt"></i> ConfigServer Security & Firewall</div>

    <div class="csf-main-nav-wrapper">
        <div id="btn-quick-actions" class="csf-nav-button summary-status" onclick="loadContent('manualcheck', this, 'content-quick-actions');"><i class="fas fa-bolt"></i> Quick Actions & Status</div>
        <div id="btn-config" class="csf-nav-button summary-config" onclick="loadContent('conf', this, 'content-config');"><i class="fas fa-cog"></i> CSF Configuration & Lists</div>
        <div id="btn-lfd" class="csf-nav-button summary-lfd" onclick="loadContent('lfdstatus', this, 'content-lfd');"><i class="fas fa-heartbeat"></i> Login Failure Daemon (LFD)</div>
        <div id="btn-log" class="csf-nav-button summary-log" onclick="loadContent('servercheck', this, 'content-log');"><i class="fas fa-search"></i> Diagnostics & Logs</div>
    </div> 
    
    <div class="csf-sub-nav-content" id="content-quick-actions">
        <div class="csf-grid">
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('restart', this, null);" class="csf-button csf-status"><i class="fas fa-sync-alt"></i> RESTART CSF & LFD</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('manualcheck', this, null);" class="csf-button csf-header"><i class="fas fa-info-circle"></i> UPGRADE / STATUS INFO</a></div>
        </div>
    </div>

    <div class="csf-sub-nav-content" id="content-config">
        <div class="csf-grid">
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('conf', this, null);" class="csf-button csf-config-btn"><i class="fas fa-file-alt"></i> Main Configuration</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('allow', this, null);" class="csf-button csf-config-btn"><i class="fas fa-user-check"></i> Firewall Allow IP's</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('deny', this, null);" class="csf-button csf-config-btn"><i class="fas fa-ban"></i> Firewall Deny IP's</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('temp', this, null);" class="csf-button csf-config-btn"><i class="fas fa-clock"></i> Temporary Entries</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('sips', this, null);" class="csf-button csf-config-btn"><i class="fas fa-server"></i> Deny Server IP's</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('profiles', this, null);" class="csf-button csf-config-btn"><i class="fas fa-users-cog"></i> Firewall Profiles</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('status', this, null);" class="csf-button csf-config-btn"><i class="fas fa-list-ol"></i> View Iptables Rules</a></div>
        </div>
    </div>

    <div class="csf-sub-nav-content" id="content-lfd">
        <div class="csf-grid">
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('lfdstatus', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-check-circle"></i> LFD Status</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('lfdrestart', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-redo"></i> Restart LFD Only</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('lfd', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-file-code"></i> Edit LFD Ignore Files</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('dyndns', this, null);" class="csf-button csf-lfd-btn"><i class="fas fa-globe"></i> LFD Dynamic DNS</a></div>
        </div>
    </div>
    
    <div class="csf-sub-nav-content" id="content-log">
        <div class="csf-grid">
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('servercheck', this, null);" class="csf-button csf-log-btn"><i class="fas fa-shield-alt"></i> Check Server Security</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('logtail', this, null);" class="csf-button csf-log-btn"><i class="fas fa-terminal"></i> Watch System Logs</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('loggrep', this, null);" class="csf-button csf-log-btn"><i class="fas fa-search-plus"></i> Search System Logs</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('viewports', this, null);" class="csf-button csf-log-btn"><i class="fas fa-network-wired"></i> View Listening Ports</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('viewlogs', this, null);" class="csf-button csf-log-btn"><i class="fas fa-file-alt"></i> View Iptables Log</a></div>
            <div class="csf-grid-item"><a href="#" onclick="return loadContent('csftest', this, null);" class="csf-button csf-log-btn"><i class="fas fa-vial"></i> Test Iptables</a></div>
        </div>
    </div>
</div>

<div class="l-center units" style="margin:1em;text-align: center;">
    <div style="text-align: right; width: 90%; max-width: 1200px; margin: 0 auto 10px auto;">
        <a href="https://vvcares.com/?CSF-UI" style="color: #666; font-size: 0.9em; text-decoration: none;">Simplified by VVCARES</a>
    </div>

    <iframe scrolling='auto' name='myiframe' id='myiframe' frameborder='0' width='90%' 
        onload='resizeIframe(this);' 
        sandbox="allow-scripts allow-forms allow-same-origin">
    </iframe>
</div>

<script>
/**
 * Highly minimized JS logic. Variable names are kept short for minimal line count.
 * (a = queryString, e = element, t = targetContentId)
 */
function loadContent(a,e,t){
    if(a!==null&&typeof a!=='string')a='';
    const f=document.getElementById('csf_submit_form'),
    i=document.getElementById('csf_action_input');
    
    if(a){
        f.action='/list/csf/frame.php'+(a.includes('=')?'?'+a:'');
        i.name=a.includes('=')?'':'action';
        i.value=a.includes('=')?'':a;
        f.target='myiframe';
        f.submit();
    }
    
    // Tab switching logic
    if(t){
        document.querySelectorAll('.csf-nav-button').forEach(b=>b.classList.remove('active'));
        document.querySelectorAll('.csf-sub-nav-content').forEach(c=>c.classList.remove('active'));
        e&&e.classList.add('active');
        document.getElementById(t)&&document.getElementById(t).classList.add('active');
    }
    return!1;
}

function resizeIframe(o){try{o.contentWindow.document.body.scrollHeight&&(o.style.height=o.contentWindow.document.body.scrollHeight+20+'px')}catch(e){console.warn("Could not resize iframe:",e)}}

document.addEventListener('DOMContentLoaded', function() {
    const h=window.location.hash.substring(1);
    let b='btn-quick-actions',c='content-quick-actions',a='manualcheck';
    
    if(h){
        const p=document.getElementById('btn-'+h),q=document.getElementById('content-'+h);
        if(p&&q){
            b='btn-'+h;c='content-'+h;
            if(h==='config')a='conf';else if(h==='lfd')a='lfdstatus';else if(h==='log')a='servercheck';
        }
    }
    
    const ib=document.getElementById(b);ib&&ib.classList.add('active');
    const ic=document.getElementById(c);ic&&ic.classList.add('active');
    loadContent(a,null,null);
    
    document.querySelectorAll('.csf-nav-button').forEach(button=>{button.addEventListener('click',function(){history.pushState(null,null,'#'+this.id.replace('btn-',''))})});
});
</script>

<?php
// Final footer include
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.php');
?>

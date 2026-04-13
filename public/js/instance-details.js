// Auto-refresh QR Code if pending
if (typeof currentInstanceStatus !== 'undefined' && currentInstanceStatus === 'pending') {
    setInterval(() => {
        fetch(`/instance/${currentInstanceId}/check`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'connected') {
                    location.reload();
                }
            })
            .catch(err => console.error('Status check failed:', err));
    }, 5000);
}

function startInstance() {
    alert('Start Instance functionality');
}

function restartInstance() {
    if (confirm('Restart instance?')) {
        location.reload();
    }
}

function troubleshootInstance() {
    alert('Check Baileys server logs for details');
}

function refreshInfo() {
    location.reload();
}

function updateDelaySettings(event) {
    event.preventDefault();
    alert('Delay settings updated!');
}
// Enable Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// File input customization
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            const label = fileInput.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    }
});

// PWA Install Prompt
let deferredPrompt;
const installButton = document.createElement('button');
installButton.textContent = 'Install ReadHub App';
installButton.className = 'btn btn-primary btn-sm me-2';
installButton.style.display = 'none';
installButton.id = 'installButton';

// Add install button to navbar
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar-nav');
    if (navbar) {
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.appendChild(installButton);
        navbar.appendChild(li);
    }
});

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installButton.style.display = 'block';
    
    installButton.addEventListener('click', (e) => {
        installButton.style.display = 'none';
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
                installButton.style.display = 'block';
            }
            deferredPrompt = null;
        });
    });
});

window.addEventListener('appinstalled', (evt) => {
    console.log('ReadHub PWA was installed');
    installButton.style.display = 'none';
});

// Network status detection
window.addEventListener('online', function() {
    console.log('Connection restored');
    // You can add a notification here
});

window.addEventListener('offline', function() {
    console.log('Connection lost - working offline');
    // You can add a notification here
});

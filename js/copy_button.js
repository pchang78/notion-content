document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-button');
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const shortcode = this.getAttribute('data-shortcode');
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = shortcode;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                alert('Shortcode copied to clipboard!');
            });
        });
    });
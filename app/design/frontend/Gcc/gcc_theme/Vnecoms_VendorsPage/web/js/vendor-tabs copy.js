    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-link');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                tabs.forEach(t => t.classList.remove('border-blue-600', 'text-blue-600'));
                tabs.forEach(t => t.classList.add('text-gray-600'));
                contents.forEach(c => c.classList.add('hidden'));

                this.classList.add('border-blue-600', 'text-blue-600');
                this.classList.remove('text-gray-600');
                
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.remove('hidden');
            });
        });
    });

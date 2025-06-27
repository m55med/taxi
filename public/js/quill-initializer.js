document.addEventListener('DOMContentLoaded', function() {
    // This script finds all instances of the discussion editor and initializes Quill on them.
    // It's designed to work even if the form is loaded dynamically.

    const initializeQuill = (container) => {
        const editorElement = container.querySelector('.discussion-editor');
        const form = container.querySelector('form');
        const notesInput = form ? form.querySelector('input[name="notes"]') : null;

        if (editorElement && form && notesInput && !editorElement.quill) {
            const quill = new Quill(editorElement, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link']
                    ]
                }
            });

            form.addEventListener('submit', function() {
                notesInput.value = quill.root.innerHTML;
            });
        }
    };

    // Initialize for any editors already on the page
    document.querySelectorAll('.discussion-form-container').forEach(initializeQuill);

    // If you load forms dynamically, you might need to re-run this.
    // For simple show/hide with Alpine.js, this should be sufficient.
    // A more robust solution for dynamic content would use MutationObserver.
}); 
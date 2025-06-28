<?php flash('kb_message'); ?>

<input type="hidden" name="id" value="<?= htmlspecialchars($data['article']['id'] ?? '') ?>">

<div class="mb-6">
    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Article Title:</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($data['article']['title'] ?? '') ?>" class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
</div>

<div class="mb-6">
    <label for="ticket_code_id" class="block text-gray-700 text-sm font-bold mb-2">Link to Ticket Code (Optional):</label>
    <select id="ticket_code_id" name="ticket_code_id" class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">-- None --</option>
        <?php foreach ($data['ticket_codes'] as $code) : ?>
            <option value="<?= $code['id'] ?>" <?= (isset($data['article']['ticket_code_id']) && $data['article']['ticket_code_id'] == $code['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($code['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-6">
    <label for="editor" class="block text-gray-700 text-sm font-bold mb-2">Content:</label>
    <input name="content" type="hidden">
    <div id="editor" class="bg-white rounded-lg">
        <?= $data['article']['content'] ?? '' ?>
    </div>
</div>

<!-- Include Quill stylesheet -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />

<!-- Include the Quill library -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- Initialize Quill editor -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{'list': 'ordered'}, {'list': 'bullet'}],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Sync Quill content with hidden input before form submission
        const form = document.getElementById('kb-form');
        if(form) {
            form.addEventListener('submit', function() {
                const contentInput = document.querySelector('input[name=content]');
                contentInput.value = quill.root.innerHTML;
            });
        }
    });
</script> 
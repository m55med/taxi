<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi</title>
    
    <!-- AlpineJS Collapse Plugin (must be loaded BEFORE Alpine core) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- AlpineJS Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Quill editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS files -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/discussions.css">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            [x-cloak] { display: none !important; }
        }
        .online-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #9ca3af; /* gray-400 for offline */
            margin-right: 8px;
        }
        .online-badge.active {
            background-color: #22c55e; /* green-500 for online */
        }
    </style>
    
    <script src="<?= URLROOT ?>/public/js/app.js" defer></script>

    <!-- Alpine.js Component Definitions -->
    <script>
        document.addEventListener('alpine:init', () => {
            <?php if (isset($driver)): ?>
            // Component for the driver details page
            Alpine.data('driverDetails', () => ({
                driverId: <?= json_encode($driver['id'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                driver: <?= json_encode($driver, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                documents: <?= json_encode($driverDocuments ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                unassignedDocuments: <?= json_encode($unassignedDocuments ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                newDocumentId: '',
                isModalOpen: false,
                documentToDeleteId: null,
                openModal(docId) {
                    this.documentToDeleteId = docId;
                    this.isModalOpen = true;
                },
                closeModal() {
                    this.isModalOpen = false;
                    this.documentToDeleteId = null;
                },
                confirmRemoveDocument() {
                    if (this.documentToDeleteId) {
                        this.manageDocument('remove', this.documentToDeleteId);
                    }
                    this.closeModal();
                },
                async manageDocument(action, docTypeId, status = '', note = null) {
                    const formData = new FormData();
                    formData.append('driver_id', this.driverId);
                    formData.append('action', action);
                    formData.append('doc_type_id', docTypeId);
                    if (status) formData.append('status', status);
                    if (note !== null) formData.append('note', note);
                    try {
                        const response = await fetch(`<?= URLROOT ?>/drivers/document/manage`, { method: 'POST', body: formData });
                        const result = await response.json();
                        if (result.success) {
                            this.documents = result.documents;
                            this.unassignedDocuments = result.unassigned;
                            this.driver = result.driver;
                            this.newDocumentId = '';
                            toastr.success('Documents updated successfully!');
                        } else {
                            toastr.error(result.message || 'An error occurred.');
                        }
                    } catch (error) {
                        toastr.error('A network error occurred.');
                    }
                },
                addDocument() {
                    if (!this.newDocumentId) return;
                    this.manageDocument('upsert', this.newDocumentId, 'missing', '');
                },
                updateDocument(doc) {
                    this.manageDocument('upsert', doc.id, doc.status, doc.note);
                },
                removeDocument(docId) {
                    this.openModal(docId);
                }
            }));
            <?php endif; ?>
        });
    </script>
</head>
<body class="bg-gray-100" dir="ltr">
    <?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include __DIR__ . '/nav.php'; 
    ?>
    <!-- Main Content Container -->

</body>
</html> 
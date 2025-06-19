document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('upload-form');
    if (!uploadForm) {
        console.error('Upload form not found!');
        return;
    }

    const fileInput = document.getElementById('file');
    const fileNameDisplay = document.getElementById('file-name');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const statsContainer = document.getElementById('stats-container');
    const insertedCount = document.getElementById('inserted-count');
    const updatedCount = document.getElementById('updated-count');
    const errorCount = document.getElementById('error-count');
    const errorDetails = document.getElementById('error-details');
    const errorMessage = document.getElementById('error-message');
    const submitButton = document.getElementById('submit-button');

    const CHUNK_SIZE = 500;

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const file = fileInput.files[0];
        if (file) {
            submitButton.disabled = true;
            handleFile(file).finally(() => {
                submitButton.disabled = false;
            });
        } else {
            showError('الرجاء اختيار ملف أولاً.');
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileNameDisplay.textContent = `الملف المحدد: ${file.name}`;
            resetUI();
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    async function handleFile(file) {
        resetUI();
        const fileData = await readFileAsync(file);
        const jsonData = parseExcelData(fileData);

        if (jsonData && jsonData.length > 0) {
            await processInChunks(jsonData);
        } else {
            showError('الملف فارغ أو لا يحتوي على بيانات صالحة.');
        }
    }

    function readFileAsync(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = err => {
                showError('حدث خطأ أثناء قراءة الملف.');
                reject(err);
            };
            reader.readAsArrayBuffer(file);
        });
    }

    function parseExcelData(data) {
        try {
            const workbook = XLSX.read(new Uint8Array(data), { type: 'array', cellDates: true, sheetStubs: true });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            return XLSX.utils.sheet_to_json(worksheet, { raw: false, defval: null });
        } catch (err) {
            showError('فشل في قراءة أو تحليل الملف. تأكد من أنه ملف Excel أو CSV صالح.');
            console.error(err);
            return null;
        }
    }

    async function processInChunks(data) {
        progressContainer.classList.remove('hidden');
        statsContainer.classList.remove('hidden');

        const chunks = [];
        for (let i = 0; i < data.length; i += CHUNK_SIZE) {
            chunks.push(data.slice(i, i + CHUNK_SIZE));
        }

        let totalStats = { inserted: 0, updated: 0, errors: 0 };

        for (let i = 0; i < chunks.length; i++) {
            const chunk = chunks[i];
            const progress = ((i + 1) / chunks.length) * 100;
            progressText.textContent = `جاري معالجة الدفعة ${i + 1} من ${chunks.length}...`;

            try {
                const response = await fetch('/taxi/trips/process', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ trips: chunk })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || `خطأ في الشبكة: ${response.statusText}`);
                }

                const result = await response.json();
                if (result.status === 'success') {
                    totalStats.inserted += result.stats.inserted;
                    totalStats.updated += result.stats.updated;
                    totalStats.errors += result.stats.errors;
                } else {
                    throw new Error(result.error || 'حدث خطأ غير معروف في الخادم.');
                }
            } catch (error) {
                totalStats.errors += chunk.length;
                showError(`خطأ في معالجة الدفعة ${i + 1}: ${error.message}`);
                console.error(error);
            } finally {
                updateUI(totalStats, progress);
            }
        }
        progressText.textContent = 'اكتملت المعالجة!';
    }

    function updateUI(stats, progress) {
        insertedCount.textContent = stats.inserted;
        updatedCount.textContent = stats.updated;
        errorCount.textContent = stats.errors;
        progressBar.style.width = `${progress}%`;
    }

    function resetUI() {
        progressContainer.classList.add('hidden');
        statsContainer.classList.add('hidden');
        errorDetails.classList.add('hidden');
        progressBar.style.width = '0%';
        progressText.textContent = '';
        insertedCount.textContent = '0';
        updatedCount.textContent = '0';
        errorCount.textContent = '0';
    }

    function showError(message) {
        errorDetails.classList.remove('hidden');
        errorMessage.textContent = message;
    }
});

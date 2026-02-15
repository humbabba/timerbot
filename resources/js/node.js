/**
 * Wave form handling - AJAX submission and URL fetching
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('wave-form');
    const runButton = document.getElementById('run-button');
    const loadingContainer = document.getElementById('loading-container');
    const resultContainer = document.getElementById('result-container');
    const resultIframe = document.getElementById('result-output');
    const errorContainer = document.getElementById('error-container');
    const downloadButton = document.getElementById('download-button');

    let currentImageUrl = null;

    // Set content in the iframe with default browser styles
    function setIframeContent(html) {
        // Escape for srcdoc attribute
        const escaped = html.replace(/&/g, '&amp;').replace(/"/g, '&quot;');

        const docContent = '<!DOCTYPE html><html><head><style>body { font-family: sans-serif; font-size: 12pt; }</style></head><body>' + html + '</body></html>';

        resultIframe.srcdoc = docContent;

        // Adjust iframe height to content after load
        resultIframe.onload = function() {
            const doc = resultIframe.contentDocument || resultIframe.contentWindow.document;
            const height = doc.body.scrollHeight;
            resultIframe.style.height = Math.max(300, height + 40) + 'px';
        };
    }

    // Set image in the iframe
    function setIframeImage(imageUrl) {
        const docContent = '<!DOCTYPE html><html><head><style>body { font-family: sans-serif; font-size: 12pt; margin: 0; padding: 16px; display: flex; justify-content: center; } img { max-width: 100%; height: auto; }</style></head><body><img src="' + imageUrl + '" alt="Generated image"></body></html>';

        resultIframe.srcdoc = docContent;

        // Adjust iframe height to content after load
        resultIframe.onload = function() {
            const doc = resultIframe.contentDocument || resultIframe.contentWindow.document;
            const height = doc.body.scrollHeight;
            resultIframe.style.height = Math.max(300, height + 40) + 'px';
        };
    }

    // Get iframe content for copying
    function getIframeBody() {
        const doc = resultIframe.contentDocument || resultIframe.contentWindow.document;
        return doc.body;
    }

    // Generate image via OpenAI
    async function generateImage() {
        const generateImageUrl = form.dataset.generateImageUrl;
        if (!generateImageUrl) {
            errorContainer.innerHTML = 'Image generation URL not configured';
            errorContainer.classList.remove('hidden');
            return;
        }

        // Show loading
        errorContainer.classList.add('hidden');
        resultContainer.classList.add('hidden');
        loadingContainer.classList.remove('hidden');
        runButton.disabled = true;
        runButton.textContent = 'Generating Image...';

        try {
            const formData = new FormData(form);
            const response = await fetch(generateImageUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.debug_prompt) {
                console.log('[Image Generation] API Prompt:', data.debug_prompt);
            }

            if (response.ok && data.success) {
                currentImageUrl = data.image_url;
                setIframeImage(data.image_url);
                let imageLabel = 'AI Generated Image';
                const metaParts = [];
                if (data.image_width && data.image_height) {
                    metaParts.push(data.image_width + ' x ' + data.image_height + ' px');
                }
                if (data.image_filesize) {
                    const kb = data.image_filesize / 1024;
                    metaParts.push(kb >= 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB');
                }
                if (metaParts.length) {
                    imageLabel += ' \u2014 ' + metaParts.join(', ');
                }
                document.getElementById('word-count').textContent = imageLabel;
                if (downloadButton) {
                    downloadButton.classList.remove('hidden');
                }
                resultContainer.classList.remove('hidden');
            } else {
                errorContainer.innerHTML = data.error || 'Failed to generate image';
                errorContainer.classList.remove('hidden');
            }
        } catch (error) {
            errorContainer.innerHTML = 'Network error: ' + error.message;
            errorContainer.classList.remove('hidden');
        } finally {
            loadingContainer.classList.add('hidden');
            runButton.disabled = false;
            runButton.textContent = 'Run';
        }
    }

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Hide previous results/errors, show loading
            errorContainer.classList.add('hidden');
            resultContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');
            runButton.disabled = true;
            runButton.textContent = 'Running...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.debug_prompt) {
                    console.log('[Node Run] API Prompt:', data.debug_prompt);
                }

                if (response.ok && data.success) {
                    // Clear image state for text results
                    currentImageUrl = null;
                    if (downloadButton) {
                        downloadButton.classList.add('hidden');
                    }

                    // Convert any remaining markdown links [text](url) to HTML
                    let html = data.result.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
                    // Convert <strong> to <b> for better compatibility with rich text editors
                    html = html.replace(/<strong>/g, '<b>').replace(/<\/strong>/g, '</b>');
                    // Convert <em> to <i> for better compatibility
                    html = html.replace(/<em>/g, '<i>').replace(/<\/em>/g, '</i>');
                    // Replace em dashes with period + space and capitalize next letter
                    html = html.replace(/\s*—\s*(\w)/g, (match, nextChar) => '. ' + nextChar.toUpperCase());

                    setIframeContent(html);

                    // Calculate and display word count
                    setTimeout(() => {
                        const text = getIframeBody().innerText;
                        const wordCount = text.trim().split(/\s+/).filter(word => word.length > 0).length;
                        document.getElementById('word-count').textContent = wordCount.toLocaleString() + ' words';
                    }, 100);

                    resultContainer.classList.remove('hidden');
                } else {
                    errorContainer.innerHTML = data.error || 'An error occurred';
                    errorContainer.classList.remove('hidden');
                }
            } catch (error) {
                errorContainer.innerHTML = 'Network error: ' + error.message;
                errorContainer.classList.remove('hidden');
            } finally {
                loadingContainer.classList.add('hidden');
                runButton.disabled = false;
                runButton.textContent = 'Run';
            }
        });
    }

    // Download image function via backend proxy
    window.downloadImage = function() {
        if (!currentImageUrl) return;

        const nodeName = document.querySelector('h1')?.textContent?.trim() || 'image';
        const slug = nodeName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const timestamp = new Date().toISOString().slice(0, 10);
        const filename = slug + '-' + timestamp + '.jpg';

        const downloadUrl = '/download-image?url=' + encodeURIComponent(currentImageUrl) + '&filename=' + encodeURIComponent(filename);
        window.location.href = downloadUrl;
    };

    // Convert images to inline data URIs for clipboard compatibility
    function convertImagesToDataUri(iframeDoc) {
        const images = iframeDoc.body.querySelectorAll('img');
        const originals = [];

        images.forEach(img => {
            originals.push({ img, src: img.src });
            try {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                img.src = canvas.toDataURL('image/jpeg', 0.8);
            } catch (e) {
                console.warn('Could not convert image to data URI:', e);
            }
        });

        return originals;
    }

    function restoreImageSrcs(originals) {
        originals.forEach(({ img, src }) => { img.src = src; });
    }

    // Copy result function
    window.copyResult = function(btn) {
        const originalText = btn.textContent;
        const doc = resultIframe.contentDocument || resultIframe.contentWindow.document;

        // Convert images to data URIs so they paste correctly into external apps
        const originals = convertImagesToDataUri(doc);

        const range = document.createRange();
        range.selectNodeContents(doc.body);

        const selection = resultIframe.contentWindow.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        resultIframe.contentDocument.execCommand('copy');
        selection.removeAllRanges();

        // Restore original image sources
        restoreImageSrcs(originals);

        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = originalText, 2000);
    };

    // File input preview and validation
    window.previewFileInput = function(input) {
        const file = input.files[0];
        const fieldName = input.name.match(/inputs\[([^\]]+)\]/)?.[1];
        const previewContainer = document.getElementById('preview_' + fieldName);

        if (!previewContainer) return;

        if (!file) {
            previewContainer.classList.add('hidden');
            return;
        }

        // Validate type
        if (!file.type.startsWith('image/')) {
            input.value = '';
            previewContainer.classList.add('hidden');
            errorContainer.innerHTML = 'Please select an image file (JPEG, PNG, GIF, WebP)';
            errorContainer.classList.remove('hidden');
            return;
        }

        // Validate size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            input.value = '';
            previewContainer.classList.add('hidden');
            errorContainer.innerHTML = 'File size must be under 10MB';
            errorContainer.classList.remove('hidden');
            return;
        }

        errorContainer.classList.add('hidden');

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = previewContainer.querySelector('img');
            img.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    };

    // URL fetch function
    window.fetchUrlContent = async function() {
        const fetchButton = document.getElementById('fetch-url-button');
        if (!fetchButton) return;

        const sourceFieldId = fetchButton.dataset.sourceField;
        const targetFieldId = fetchButton.dataset.targetField;
        const fetchUrl = fetchButton.dataset.fetchUrl;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const sourceField = document.getElementById(sourceFieldId);
        const targetField = document.getElementById(targetFieldId);

        if (!sourceField || !targetField) {
            errorContainer.innerHTML = 'Source or target field not found';
            errorContainer.classList.remove('hidden');
            return;
        }

        const url = sourceField.value.trim();
        if (!url) {
            errorContainer.innerHTML = 'Please enter a URL in the source field';
            errorContainer.classList.remove('hidden');
            return;
        }

        // Show loading state
        const originalText = fetchButton.textContent;
        fetchButton.disabled = true;
        fetchButton.textContent = 'Fetching...';
        errorContainer.classList.add('hidden');

        try {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('url', url);

            const response = await fetch(fetchUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                targetField.value = data.content || 'The request was successful, but no content was retrieved.';
            } else {
                targetField.value = '';
                setIframeContent('');
                resultContainer.classList.add('hidden');
                errorContainer.innerHTML = data.error || 'Failed to fetch URL';
                errorContainer.classList.remove('hidden');
            }
        } catch (error) {
            errorContainer.innerHTML = 'Network error: ' + error.message;
            errorContainer.classList.remove('hidden');
        } finally {
            fetchButton.disabled = false;
            fetchButton.textContent = originalText;
        }
    };
});

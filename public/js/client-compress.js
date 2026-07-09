/**
 * Client-Side Image Compressor
 * ----------------------------------------------------------------------------
 * Compresses images IN THE BROWSER before upload using HTML5 Canvas.
 * This bypasses PHP upload_max_filesize limits entirely — the file sent
 * to the server is already small (under 2MB).
 *
 * Works with: JPEG, PNG, GIF, WEBP
 * Output: JPEG at 85% quality, max 1920px (configurable per field)
 *
 * Usage:
 *   <input type="file" name="photo" data-compress="1200" data-maxsize="1500">
 *   <script src="/js/client-compress.js"></script>
 *
 * The data-compress attribute sets max dimension (px).
 * The data-maxsize attribute sets max file size (KB).
 */
(function() {
    'use strict';

    function compressImage(file, maxDimension, maxSizeKB, callback) {
        // Not an image — return original
        if (!file.type.startsWith('image/')) {
            callback(file);
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');

                // Calculate new dimensions (maintain aspect ratio)
                var width = img.width;
                var height = img.height;
                if (width > maxDimension || height > maxDimension) {
                    var ratio = Math.min(maxDimension / width, maxDimension / height);
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                canvas.width = width;
                canvas.height = height;

                // White background (for PNG transparency → JPEG)
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);

                // Draw the image
                ctx.drawImage(img, 0, 0, width, height);

                // Progressive quality compression
                var quality = 0.85;
                var minQuality = 0.40;
                var step = 0.10;

                function tryCompress() {
                    canvas.toBlob(function(blob) {
                        var sizeKB = blob.size / 1024;
                        if (sizeKB <= maxSizeKB || quality <= minQuality) {
                            // Done — create a new File object
                            var compressedFile = new File([blob], file.name.replace(/\.(png|gif|webp)$/i, '.jpg'), {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            console.log('[ClientCompress] ' + (file.size/1024).toFixed(0) + 'KB → ' + (compressedFile.size/1024).toFixed(0) + 'KB (q=' + quality.toFixed(2) + ', ' + width + 'x' + height + ')');
                            callback(compressedFile);
                        } else {
                            quality -= step;
                            tryCompress();
                        }
                    }, 'image/jpeg', quality);
                }

                tryCompress();
            };
            img.onerror = function() {
                console.warn('[ClientCompress] Could not load image — sending original');
                callback(file);
            };
            img.src = e.target.result;
        };
        reader.onerror = function() {
            console.warn('[ClientCompress] Could not read file — sending original');
            callback(file);
        };
        reader.readAsDataURL(file);
    }

    // Hook into all file inputs with data-compress attribute
    function init() {
        var inputs = document.querySelectorAll('input[type="file"][data-compress]');
        inputs.forEach(function(input) {
            var form = input.closest('form');
            if (!form || input.dataset.compressInit) return;
            input.dataset.compressInit = '1';

            var maxDim = parseInt(input.dataset.compress) || 1920;
            var maxSizeKB = parseInt(input.dataset.maxsize) || 1500;

            // Create status indicator
            var status = document.createElement('div');
            status.className = 'compress-status';
            status.style.cssText = 'font-size:0.75rem;color:#6b7280;margin-top:4px;display:none;';
            input.parentNode.appendChild(status);

            form.addEventListener('submit', function(e) {
                if (input.dataset.compressing === '1') {
                    e.preventDefault();
                    return false;
                }

                var files = input.files;
                if (!files || files.length === 0) return;

                // For multiple file inputs, compress each file
                if (input.hasAttribute('multiple')) {
                    e.preventDefault();
                    input.dataset.compressing = '1';
                    status.style.display = 'block';
                    status.innerHTML = '⏳ Compressing ' + files.length + ' images...';
                    status.style.color = '#d97706';

                    var compressedFiles = [];
                    var done = 0;
                    var dt = new DataTransfer();

                    function processNext(index) {
                        if (index >= files.length) {
                            // All done — replace files and submit
                            input.files = dt.files;
                            input.dataset.compressing = '';
                            status.innerHTML = '✓ Compressed ' + compressedFiles.length + ' images';
                            status.style.color = '#059669';
                            setTimeout(function() { form.submit(); }, 200);
                            return;
                        }

                        compressImage(files[index], maxDim, maxSizeKB, function(compressed) {
                            dt.items.add(compressed);
                            compressedFiles.push(compressed);
                            done++;
                            status.innerHTML = '⏳ Compressing ' + done + '/' + files.length + '...';
                            processNext(index + 1);
                        });
                    }

                    processNext(0);
                } else {
                    // Single file
                    e.preventDefault();
                    input.dataset.compressing = '1';
                    status.style.display = 'block';
                    status.innerHTML = '⏳ Compressing image...';
                    status.style.color = '#d97706';

                    compressImage(files[0], maxDim, maxSizeKB, function(compressed) {
                        var dt = new DataTransfer();
                        dt.items.add(compressed);
                        input.files = dt.files;
                        input.dataset.compressing = '';
                        status.innerHTML = '✓ Compressed: ' + (files[0].size/1024).toFixed(0) + 'KB → ' + (compressed.size/1024).toFixed(0) + 'KB';
                        status.style.color = '#059669';
                        setTimeout(function() { form.submit(); }, 200);
                    });
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

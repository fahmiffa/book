<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Informasi Pendaftaran - {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    </head>
    <body class="antialiased bg-gray-50 dark:bg-gray-900 min-h-screen">
        <livewire:booking.check :uuid="request()->route('uuid')" />

        <script>
            async function downloadPDF() {
                var jsPDF = window.jspdf.jsPDF;
                var doc = new jsPDF({ unit: 'mm', format: [58, 150] });
                var w = 58;
                var y = 8;
                var cx = w / 2;

                doc.setFont('courier', 'bold');
                doc.setFontSize(12);
                doc.text(document.title.split(' - ').pop() || 'Booking', cx, y, { align: 'center' });
                y += 5;

                doc.setFontSize(7);
                doc.text('BUKTI ANTRIAN LAYANAN', cx, y, { align: 'center' });
                y += 3;
                doc.setDrawColor(0);
                doc.setLineDashPattern([1, 1], 0);
                doc.line(4, y, w - 4, y);
                y += 6;

                doc.setFontSize(20);
                doc.setFont('courier', 'bold');
                var code = document.getElementById('thermal-receipt').querySelector('.text-3xl');
                doc.text(code ? code.textContent.trim() : '-', cx, y, { align: 'center' });
                y += 5;

                doc.setFontSize(6);
                doc.text('NOMOR ANTRIAN', cx, y, { align: 'center' });
                y += 3;
                doc.line(4, y, w - 4, y);
                y += 3;

                // QR CODE PDF - Converting SVG to PNG via Canvas for compatibility
                var qrContainer = document.getElementById('qr-code-canvas-container');
                var svg = qrContainer ? qrContainer.querySelector('svg') : null;
                if (svg) {
                    var canvas = document.createElement('canvas');
                    canvas.width = 300;
                    canvas.height = 300;
                    var ctx = canvas.getContext('2d');
                    var svgData = new XMLSerializer().serializeToString(svg);
                    var img = new Image();
                    var svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
                    var url = URL.createObjectURL(svgBlob);
                    
                    await new Promise((resolve) => {
                        img.onload = resolve;
                        img.src = url;
                    });
                    
                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    var imgData = canvas.toDataURL("image/png");
                    doc.addImage(imgData, 'PNG', cx - 15, y, 30, 30);
                    y += 33;
                    URL.revokeObjectURL(url);
                }

                doc.line(4, y, w - 4, y);
                y += 5;

                doc.setFontSize(8);
                doc.setFont('courier', 'normal');
                var rows = document.querySelectorAll('#thermal-receipt .space-y-1 > div');
                rows.forEach(function(row) {
                    var txt = row.textContent.trim();
                    if (txt === '') {
                        doc.line(4, y, w - 4, y);
                        y += 4;
                    } else {
                        var spans = row.querySelectorAll('span');
                        if (spans.length === 2) {
                            doc.text(spans[0].textContent.trim(), 4, y);
                            doc.text(spans[1].textContent.trim(), w - 4, y, { align: 'right' });
                        } else {
                            if (row.classList.contains('font-bold')) {
                                doc.setFont('courier', 'bold');
                            }
                            if (row.classList.contains('text-center')) {
                                doc.text(txt, cx, y, { align: 'center' });
                            } else {
                                doc.text(txt, 4, y);
                            }
                            doc.setFont('courier', 'normal');
                        }
                        y += 4;
                    }
                });

                y += 2;
                doc.line(4, y, w - 4, y);
                y += 4;
                doc.setFontSize(6);
                doc.text('Harap Datang 10 Menit', cx, y, { align: 'center' });
                y += 3;
                doc.text('Sebelum Waktu Layanan', cx, y, { align: 'center' });

                doc.save('Bukti-Pendaftaran.pdf');
            }

            function printPOS() {
                var thermalEl = document.getElementById('thermal-receipt');
                if (!thermalEl) return;
                var thermalContent = thermalEl.innerHTML;

                var posStyle = '@page { margin: 0; }' +
                    ' body { font-family: "Courier New", Courier, monospace; width: 58mm; padding: 10px; margin: 0; }' +
                    ' .text-center { text-align: center; }' +
                    ' .text-lg { font-size: 14pt; }' +
                    ' .text-3xl { font-size: 30pt; }' +
                    ' .text-xs { font-size: 8pt; }' +
                    ' .font-bold { font-weight: bold; }' +
                    ' .font-black { font-weight: 900; }' +
                    ' .mb-1 { margin-bottom: 4px; }' +
                    ' .mb-2 { margin-bottom: 8px; }' +
                    ' .mb-4 { margin-bottom: 16px; }' +
                    ' .mt-2 { margin-top: 8px; }' +
                    ' .mt-4 { margin-top: 16px; }' +
                    ' .space-y-1 > * + * { margin-top: 4px; }' +
                    ' .flex { display: flex; }' +
                    ' .justify-between { justify-content: space-between; }' +
                    ' .justify-center { justify-content: center; }' +
                    ' .items-center { align-items: center; }' +
                    ' .w-full { width: 100%; }' +
                    ' .mx-auto { margin-left: auto; margin-right: auto; }' +
                    ' .inline-block { display: inline-block; }' +
                    ' .border-b { border-bottom: 1px dashed black; }' +
                    ' .border-t { border-top: 1px dashed black; }' +
                    ' .pb-2 { padding-bottom: 8px; }' +
                    ' .pt-2 { padding-top: 8px; }' +
                    ' .my-2 { margin-top: 8px; margin-bottom: 8px; }';

                var iframe = document.createElement('iframe');
                iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                document.body.appendChild(iframe);

                var doc = iframe.contentWindow.document;
                doc.open();
                doc.close();

                var styleEl = doc.createElement('style');
                styleEl.textContent = posStyle;
                doc.head.appendChild(styleEl);
                doc.body.innerHTML = thermalContent;

                setTimeout(function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(function() {
                        document.body.removeChild(iframe);
                    }, 1000);
                }, 500);
            }
        </script>
    </body>
</html>

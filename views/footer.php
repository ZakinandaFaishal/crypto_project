<script>
document.addEventListener('DOMContentLoaded', () => {
    // Cek jika kita berada di halaman dashboard
    const startBtn = document.getElementById('btn-start-record');
    if (startBtn) {
        // Hanya jalankan jika elemen perekam ada
        const stopBtn = document.getElementById('btn-stop-record');
        const sendBtn = document.getElementById('btn-send-record');
        const audioPlayback = document.getElementById('audio-playback');
        const audioForm = document.getElementById('form-audio-record');
        const statusEl = document.getElementById('record-status');

        let mediaRecorder;
        let audioChunks = [];
        let recordedAudioBlob;

        // 1. Minta Izin Mikrofon & Mulai Merekam
        startBtn.addEventListener('click', async () => {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' }); // Rekam sebagai .webm
                    
                    audioChunks = []; // Kosongkan rekaman sebelumnya
                    mediaRecorder.start();

                    mediaRecorder.ondataavailable = event => {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        // Buat file audio dari rekaman
                        recordedAudioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(recordedAudioBlob);
                        
                        // Tampilkan pemutar audio
                        audioPlayback.src = audioUrl;
                        audioPlayback.classList.remove('hidden');
                        sendBtn.classList.remove('hidden');
                    };

                    // Ganti tombol
                    startBtn.classList.add('hidden');
                    stopBtn.classList.remove('hidden');
                    statusEl.classList.remove('hidden'); // Tampilkan "Merekam..."
                    sendBtn.classList.add('hidden');
                    audioPlayback.classList.add('hidden');

                } catch (err) {
                    alert('Error: Tidak bisa mengakses mikrofon. Pastikan Anda memberi izin. ' + err.message);
                }
            } else {
                alert('Error: Browser Anda tidak mendukung perekaman audio.');
            }
        });

        // 2. Berhenti Merekam
        stopBtn.addEventListener('click', () => {
            if (mediaRecorder) {
                mediaRecorder.stop();
                // Matikan stream mic
                mediaRecorder.stream.getTracks().forEach(track => track.stop()); 
            }
            
            // Ganti tombol
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            statusEl.classList.add('hidden'); // Sembunyikan "Merekam..."
        });

        // 3. Kirim Form (Saat "Kirim Sinyal" diklik)
        audioForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Hentikan pengiriman form biasa

            if (!recordedAudioBlob) {
                alert('Anda belum merekam audio.');
                return;
            }
            
            // Tampilkan status loading
            sendBtn.disabled = true;
            sendBtn.textContent = 'Mengirim Sinyal...';

            // Ambil data form
            const formData = new FormData(audioForm);
            
            // Buat nama file unik
            const filename = `recording_${Date.now()}.webm`;
            
            // Tambahkan file audio rekaman (blob) ke formData
            formData.append('cover_audio_record', recordedAudioBlob, filename);

            // Kirim data (AJAX)
            fetch('../controllers/audio_record_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Arahkan user kembali ke dashboard
                // Kita harus cek jika ada error
                if(data.includes('status=ok')) {
                    window.location.href = '../views/dashboard.php?status=Sinyal audio berhasil direkam dan dikirim';
                } else {
                    // Tampilkan error dari PHP
                    window.location.href = '../views/dashboard.php?error=' + encodeURIComponent(data); 
                }
            })
            .catch(error => {
                window.location.href = '../views/dashboard.php?error=Gagal mengirim rekaman: ' + error;
            });
        });
    }
});
</script>
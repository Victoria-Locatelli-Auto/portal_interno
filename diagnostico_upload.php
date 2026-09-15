<?php
/**
 * Diagnóstico rápido do erro de upload
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h2>🔍 Diagnóstico do Importador</h2>";
echo "<style>body{font-family:Arial;margin:20px;}.ok{color:green;}.erro{color:red;}.aviso{color:orange;}</style>";

// 1. Pasta uploads
$upload_dir = UPLOAD_PATH;
echo "<h3>1. Pasta Uploads</h3>";
echo "Caminho: <code>$upload_dir</code><br>";
echo "Existe? " . (is_dir($upload_dir) ? "<span class='ok'>✅ SIM</span>" : "<span class='erro'>❌ NÃO</span>") . "<br>";
echo "Gravável? " . (is_writable($upload_dir) ? "<span class='ok'>✅ SIM</span>" : "<span class='erro'>❌ NÃO</span>") . "<br>";

if (!is_writable($upload_dir)) {
    echo "<p class='erro'>SOLUÇÃO: Execute <code>sudo chmod 775 $upload_dir && sudo chown www-data:www-data $upload_dir</code></p>";
}

// 2. PHP settings
echo "<h3>2. Configurações PHP</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? "<span class='ok'>ON</span>" : "<span class='erro'>OFF</span>") . "<br>";

// 3. Teste de escrita
echo "<h3>3. Teste de Escrita</h3>";
$test_file = $upload_dir . 'test_' . time() . '.txt';
if (file_put_contents($test_file, 'teste')) {
    echo "<span class='ok'>✅ Consegue gravar arquivos</span><br>";
    unlink($test_file);
} else {
    echo "<span class='erro'>❌ NÃO consegue gravar arquivos!</span><br>";
}

// 4. Formulário de teste
echo "<h3>4. Teste Rápido</h3>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_pdf' accept='.pdf'><br><br>";
echo "<button type='submit'>Testar Upload</button>";
echo "</form>";

if (isset($_FILES['test_pdf'])) {
    $file = $_FILES['test_pdf'];
    echo "<h4>Resultado:</h4>";
    echo "<pre>";
    echo "Nome: " . $file['name'] . "\n";
    echo "Tipo: " . $file['type'] . "\n";
    echo "Tamanho: " . number_format($file['size']/1024, 2) . " KB\n";
    echo "Erro: " . $file['error'] . " (" . ($file['error'] === 0 ? 'OK' : 'ERRO') . ")\n";
    echo "Temp: " . $file['tmp_name'] . "\n";
    echo "</pre>";
    
    if ($file['error'] === 0) {
        echo "<span class='ok'>✅ Upload funcionou!</span>";
    } else {
        $errors = [
            1 => 'Arquivo maior que upload_max_filesize',
            2 => 'Arquivo maior que MAX_FILE_SIZE do form',
            3 => 'Upload parcial',
            4 => 'Nenhum arquivo enviado',
            6 => 'Pasta temp não encontrada',
            7 => 'Falha ao gravar no disco',
            8 => 'Extensão PHP bloqueou'
        ];
        echo "<span class='erro'>❌ " . ($errors[$file['error']] ?? 'Erro desconhecido') . "</span>";
    }
}
?>

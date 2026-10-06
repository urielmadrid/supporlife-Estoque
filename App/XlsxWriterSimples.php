<?php
/*
==========================================================
GERADOR XLSX SEM DEPENDÊNCIA EXTERNA
==========================================================
*/

class XlsxWriterSimples
{
    private $linhas = [];

    public function addRow(array $valores)
    {
        $this->linhas[] = array_values($valores);
    }

    private function xml($valor)
    {
        return htmlspecialchars((string)$valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function coluna($numero)
    {
        $letra = '';
        while ($numero > 0) {
            $resto = ($numero - 1) % 26;
            $letra = chr(65 + $resto) . $letra;
            $numero = (int)(($numero - $resto - 1) / 26);
        }
        return $letra;
    }

    private function buildSheetXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
        $xml .= '<sheetData>';

        foreach ($this->linhas as $r => $linha) {
            $numeroLinha = $r + 1;
            $xml .= '<row r="' . $numeroLinha . '">';

            foreach ($linha as $c => $valor) {
                $ref = $this->coluna($c + 1) . $numeroLinha;
                $texto = $this->xml($valor);
                $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">'
                    . $texto
                    . '</t></is></c>';
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    private function zipAddFile($handle, $nome, $conteudo, &$central, &$offset)
    {
        $dados = $conteudo;
        $crc = crc32($dados);
        if ($crc < 0) {
            $crc += 4294967296;
        }

        $compactado = function_exists('gzdeflate')
            ? gzdeflate($dados, 6)
            : false;

        if ($compactado !== false && strlen($compactado) < strlen($dados)) {
            $dadosZip = $compactado;
            $metodo = 8; // deflate
        } else {
            $dadosZip = $dados;
            $metodo = 0; // store
        }

        $tamanhoOriginal = strlen($dados);
        $tamanhoZip = strlen($dadosZip);
        $nomeLen = strlen($nome);

        $cabecalhoLocal =
            pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                $metodo,
                0,
                0,
                $crc,
                $tamanhoZip,
                $tamanhoOriginal,
                $nomeLen,
                0
            );

        fwrite($handle, $cabecalhoLocal);
        fwrite($handle, $nome);
        fwrite($handle, $dadosZip);

        $central[] =
            pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                $metodo,
                0,
                0,
                $crc,
                $tamanhoZip,
                $tamanhoOriginal,
                $nomeLen,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $nome;

        $offset += strlen($cabecalhoLocal) + $nomeLen + $tamanhoZip;
    }

    public function output($nomeArquivo)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');

        if ($tmp === false) {
            throw new RuntimeException('Não foi possível criar o arquivo XLSX temporário.');
        }

        $handle = fopen($tmp, 'wb');

        if (!$handle) {
            @unlink($tmp);
            throw new RuntimeException('Não foi possível abrir o arquivo XLSX temporário para gravação.');
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Itens em Uso" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'
            . '</cellXfs>'
            . '</styleSheet>';

        $sheet = $this->buildSheetXml();

        $arquivos = [
            '[Content_Types].xml' => $contentTypes,
            '_rels/.rels' => $rels,
            'xl/workbook.xml' => $workbook,
            'xl/_rels/workbook.xml.rels' => $workbookRels,
            'xl/styles.xml' => $styles,
            'xl/worksheets/sheet1.xml' => $sheet
        ];

        $central = [];
        $offset = 0;

        foreach ($arquivos as $nome => $conteudo) {
            $this->zipAddFile($handle, $nome, $conteudo, $central, $offset);
        }

        $inicioCentral = $offset;

        foreach ($central as $registro) {
            fwrite($handle, $registro);
            $offset += strlen($registro);
        }

        $tamanhoCentral = $offset - $inicioCentral;
        $quantidade = count($central);

        $eocd = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $quantidade,
            $quantidade,
            $tamanhoCentral,
            $inicioCentral,
            0
        );

        fwrite($handle, $eocd);
        fclose($handle);

        if (!is_file($tmp) || filesize($tmp) <= 0) {
            @unlink($tmp);
            throw new RuntimeException('O arquivo XLSX não foi criado corretamente.');
        }

        /*
         * A exportação precisa enviar somente os bytes do XLSX.
         * Limpa buffers que possam ter sido iniciados por configurações
         * da hospedagem ou por includes anteriores, evitando corrupção
         * do arquivo baixado.
         */
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nomeArquivoSeguro = preg_replace(
            '/[^A-Za-z0-9_.-]/',
            '_',
            (string)$nomeArquivo
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nomeArquivoSeguro . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $arquivo = fopen($tmp, 'rb');

        if (!$arquivo) {
            @unlink($tmp);
            throw new RuntimeException('Não foi possível ler o arquivo XLSX gerado.');
        }

        fpassthru($arquivo);
        fclose($arquivo);

        @unlink($tmp);
        exit;
    }
}

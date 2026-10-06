<?php
/*
==========================================================
HELPER - ITENS EM USO
Reúne a regra de período e a consulta usada pela tela
e pela exportação, evitando duplicação.
==========================================================
*/

function obterPeriodoItensUso($periodo, $dataInicial = '', $dataFinal = '')
{
    $hoje = new DateTimeImmutable('today');

    switch ($periodo) {
        case 'hoje':
            $inicio = $hoje;
            $fim = $hoje->modify('+1 day');
            break;

        case '7_dias':
            $inicio = $hoje->modify('-6 days');
            $fim = $hoje->modify('+1 day');
            break;

        case '30_dias':
            $inicio = $hoje->modify('-29 days');
            $fim = $hoje->modify('+1 day');
            break;

        case 'este_mes':
            $inicio = $hoje->modify('first day of this month');
            $fim = $inicio->modify('+1 month');
            break;

        case '3_meses':
            $inicio = $hoje->modify('-2 months')->modify('first day of this month');
            $fim = $hoje->modify('+1 day');
            break;

        case 'personalizado':
            $di = DateTime::createFromFormat('Y-m-d', $dataInicial);
            $df = DateTime::createFromFormat('Y-m-d', $dataFinal);

            if (
                !$di || !$df ||
                $di->format('Y-m-d') !== $dataInicial ||
                $df->format('Y-m-d') !== $dataFinal
            ) {
                throw new InvalidArgumentException('Informe datas válidas.');
            }

            if ($dataInicial > $dataFinal) {
                throw new InvalidArgumentException('A data inicial não pode ser maior que a data final.');
            }

            $inicio = new DateTimeImmutable($dataInicial . ' 00:00:00');
            $fim = new DateTimeImmutable($dataFinal . ' 00:00:00');
            $fim = $fim->modify('+1 day');
            break;

        default:
            $inicio = $hoje->modify('-29 days');
            $fim = $hoje->modify('+1 day');
            break;
    }

    return [
        'inicio' => $inicio->format('Y-m-d H:i:s'),
        'fim' => $fim->format('Y-m-d H:i:s')
    ];
}

function consultarItensEmUso($db, $inicio, $fim)
{
    /*
     * A data de uso vem do histórico.
     *
     * EM_USO = novo padrão, semanticamente separado de SAIDA.
     * SAIDA + "Item entregue para:" = compatibilidade com registros
     * antigos criados antes desta implementação.
     *
     * A consulta considera somente itens que continuam atualmente
     * com status USO.
     */
    $sql = "
        SELECT *
        FROM (
            SELECT
                i.id_itens,
                i.nome_item,
                i.codigo_item,
                i.lote,
                i.quant_itens,
                i.local,
                i.marca_item,
                i.usuario_item,
                (
                    SELECT MAX(h.data_movimentacao)
                    FROM historico_movimentacoes h
                    WHERE h.id_item = i.id_itens
                      AND (
                          UPPER(TRIM(h.acao)) = 'EM_USO'
                          OR (
                              UPPER(TRIM(h.acao)) IN ('SAIDA', 'SAÍDA')
                              AND h.descricao LIKE 'Item entregue para:%'
                          )
                      )
                ) AS data_uso
            FROM itens i
            WHERE i.ativo = 1
              AND i.setor = 'FARMACIA'
              AND UPPER(TRIM(i.status_item)) = 'USO'
        ) AS itens_uso
        WHERE data_uso >= ? AND data_uso < ?
        ORDER BY data_uso DESC, nome_item ASC
    ";

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Erro ao preparar a consulta de itens em uso: ' . $db->error);
    }

    $stmt->bind_param('ss', $inicio, $fim);

    if (!$stmt->execute()) {
        $erro = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Erro ao consultar itens em uso: ' . $erro);
    }

    $resultado = $stmt->get_result();
    $itens = [];

    while ($row = $resultado->fetch_assoc()) {
        $itens[] = $row;
    }

    $stmt->close();

    return $itens;
}

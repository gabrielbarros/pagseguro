<?php
namespace PagSeguro;

class PagSeguroNotificacao extends PagSeguroConsulta {

    // Notificação de assinatura
    const ASSINATURA = 'preApproval';

    // Notificação de transação
    const TRANSACAO = 'transaction';

    // Função para salvar o resultado da notificacao no banco de dados
    // function($xml, $notificationType, $notificationCode, $manual) {}
    public $callback;


    // Método chamado pelo PagSeguro
    public function receberNotificacao($post) {
        // $post: notificationCode, notificationType=preApproval|transaction

        // Permitir que a sandbox envie dados via ajax para o localhost
        if ($this->sandbox) {
            header('Access-Control-Allow-Origin: ' . $this->url);
        }

        // Checar campos
        if (!isset($post['notificationCode'], $post['notificationType'])) {
            $msg = 'Faltando algum dos campos: ';
            $msg .= 'notificationCode, notificationType';
            $this->erro($msg);
        }

        $notificationCode = $post['notificationCode'];
        $notificationType = $post['notificationType'];

        // Notificação de assinatura ou transação?
        if (!in_array($notificationType,
            array(self::ASSINATURA, self::TRANSACAO))) {

            $this->erro('Campo "notificationType" inválido');
        }


        // Consultar no PagSeguro informações sobre a notificação recebida
        $xml = $this->consultar($notificationType, array(
            'notificationCode' => $notificationCode
        ));

        $salvou = $this->salvar($xml, $notificationType, $notificationCode);
        return $salvou;
    }


    public function salvar($xml, $notificationType, $notificationCode,
        $manual = false) {

        // Salvar notificação em um banco de dados, p. ex.
        if (is_callable($this->callback)) {
            $salvou = ($this->callback)(
                $xml,
                $notificationType, $notificationCode, $manual
            );

            return $salvou;
        }

        $msg = 'Informe um callback válido para determinar o que fazer ';
        $msg .= 'com a notificação recebida';
        $this->erro($msg);
    }


    public function sincronizar($notificationType, $tempo = 24, $pag = 1) {
        $qtdeSincronizada = 0;

        $initialDate = date('Y-m-d\TH:i', strtotime("-{$tempo} hours"));
        // $finalDate = date('Y-m-d\TH:i', strtotime('now'));
        $finalDate = '';

        $maxResults = 30;

        // Consultar transação
        if ($notificationType === self::TRANSACAO) {
            $url = '/v2/transactions';
        }

        // Consultar assinatura
        elseif ($notificationType === self::ASSINATURA) {
            $url = '/v2/pre-approvals';
        }

        else {
            $this->erro('$notificationType inválido');
        }

        $xmlNodeFilho = $notificationType;
        $xmlNodePai = $notificationType . 's';

        $xml = $this->request('GET', $url, array(
            'initialDate' => $initialDate,
            'finalDate' => $finalDate,
            'page' => $pag,
            'maxPageResults' => $maxResults
        ));

        // Verificar se o XML possui os campos necessários
        if (!isset($xml->totalPages, $xml->resultsInThisPage)) {
            $this->erro('Faltam alguns campos no XML #1');
        }

        $totalPages = (int) $xml->totalPages;
        $results = (int) $xml->resultsInThisPage;

        if (!$results) {
            // Nenhum resultado
            return 0;
        }

        // Mais uma validação no XML
        if (!isset($xml->{$xmlNodePai},
            $xml->{$xmlNodePai}->{$xmlNodeFilho})) {

            $this->erro('Faltam alguns campos no XML #2');
        }

        // Status que interessam para salvar no banco de dados
        $statusArr = array(
            (string) PagSeguroTransacao::PAGA,
            PagSeguroAssinatura::APROVADA,
            PagSeguroAssinatura::CANCELADA,
            PagSeguroAssinatura::CANCELADA_PELO_VENDEDOR,
            PagSeguroAssinatura::CANCELADA_PELO_USUARIO,
            PagSeguroAssinatura::EXPIRADA
        );

        foreach ($xml->{$xmlNodePai}->{$xmlNodeFilho} as $value) {
            // $value->code = código da transação/assinatura
            // $value->status = status da transação/assinatura

            if (in_array((string) $value->status, $statusArr, true)) {

                $xml_consulta = $this->consultar($notificationType, array(
                    'code' => $value->code
                ));

                // Notificações manuais serão inseridas no banco com o
                // código "manual_xxxxxx". Isso acontece quando o PagSeguro não
                // conseguir se comunicar com o site, então é necessário fazer
                // uma solicitação manual para sincronizar possíveis
                // notificações perdidas o mais rápido possível

                $notificationCode = 'manual_' . $this->randomString(32);

                $salvou = $this->salvar($xml_consulta, $notificationType,
                    $notificationCode, true);

                if ($salvou) {
                    $qtdeSincronizada++;
                }
            }
        }

        // Se houver outra página de resultados, continuar analisando
        // em busca de notificações perdidas...
        if ($pag < $totalPages) {
            $pag++;
            $qtdeSincronizada += $this->sincronizar(
                                      $notificationType, $tempo, $pag
                                  );
        }

        return $qtdeSincronizada;
    }
}

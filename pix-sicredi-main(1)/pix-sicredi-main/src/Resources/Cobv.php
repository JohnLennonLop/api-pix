<?php


namespace PixSicredi\Resources;

use PixSicredi\Api;
use PixSicredi\Http\Request;
use PixSicredi\Util\Support;
use PixSicredi\Resources\Traits\Valor;
use PixSicredi\Resources\Traits\Multa;
use PixSicredi\Resources\Traits\Juros;
use PixSicredi\Resources\Traits\Devedor;
use PixSicredi\Resources\Traits\Desconto;
use PixSicredi\Resources\Traits\Fillable;
use PixSicredi\Resources\Traits\Abatimento;
use PixSicredi\Resources\Traits\Logradouro;
use PixSicredi\Resources\Filters\CobFilters;
use PixSicredi\Resources\Traits\InfoAdicional;


class Cobv
{
    use Devedor, Logradouro, Valor, Multa, Juros, Abatimento, Desconto, InfoAdicional, Fillable;

    public Api $api;
    protected ?string $chave = null;
    protected ?string $txid = null;
    protected array $calendario = [];
    protected array $devedor = [];
    protected array $loc = [];
    protected array $valor = [];
    protected ?string $solicitacaoPagador = null;
    protected ?string $status = null;
    protected array $infoAdicionais = [];
    public Request $request;


    public function __construct(Api $api)
    {
        $this->api = $api;
    }


    public function setChave(string $chave): Cobv
    {
        if (Support::length($chave) > 77) {
            throw new \Exception('a chave pix deve ter no máximo 77 caracteres');
        }

        $this->chave = $chave;

        return $this;
    }


    public function setTxId(string $txid): Cobv
    {
        if (!preg_match('/^[a-zA-Z0-9]{26,35}$/', $txid)) {
            throw new \Exception('txid inválido, deve ser alfanumérico entre 26 e 35 caracteres');
        }

        $this->txid = $txid;

        return $this;
    }


    public function setDataVencimento(string $date): Cobv
    {
        if ( !Support::validateDate($date)){
            throw new \Exception('data de vencimento não é uma data válida');
        }
        $this->calendario['dataDeVencimento'] = $date;

        return $this;
    }


    public function setValidadeVencimento(int $dias): Cobv
    {
        $this->calendario['validadeAposVencimento'] = $dias;

        return $this;
    }


    public function setLocId(int $id): Cobv
    {
        $this->loc['id'] = $id;

        return $this;
    }


    public function setStatus(string $status): Cobv
    {
        $enum = ['ATIVA', 'REMOVIDA_PELO_USUARIO_RECEBEDOR'];

        if (!in_array(Support::upper($status), $enum)){
            throw new \Exception('status inválido, dever se "ATIVA" ou "REMOVIDA_PELO_USUARIO_RECEBEDOR"');
        }

        $this->status = Support::upper($status);

        return $this;
    }


    public function setSolicitacaoPagador(string $solicitacaopagador): Cobv
    {
        $this->solicitacaoPagador = Support::substr($solicitacaopagador, 0, 140);

        return $this;
    }


    public function create(): Cobv
    {
        $request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/cobv/' . $this->txid),
                'PUT',
                ['body' => $this->getBody()]
            );

        $this->request = $request;

        return $this;
    }


    public function update(): Cobv
    {
        $request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/cobv/' . $this->txid),
                'PATCH',
                ['body' => $this->getBody(true)]
            );

        $this->request = $request;

        return $this;
    }


    public function consult(string $txid): Cobv
    {
        $request = (new Request($this->api))
            ->call($this->api->getUrl('/cobv/' . $txid));

        $this->request = $request;

        return $this;
    }


    public function cancel(string $txid): Cobv
    {
        $request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/cobv/' . $txid),
                'PATCH',
                ['body' => ['status' => 'REMOVIDA_PELO_USUARIO_RECEBEDOR']]
            );

        $this->request = $request;

        return $this;
    }


    public function list(CobFilters $filter): Cobv
    {
        $request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/cobv/'),
                'GET',
                $filter->toArray()
            );

        $this->request = $request;

        return $this;
    }


    public function getBody(?bool $filled = true): array
    {
        $data = [
            'chave' => $this->chave,
            'txid' => $this->txid,
            'calendario' => $this->calendario,
            'devedor' => $this->devedor,
            'loc' => $this->loc,
            'valor' => $this->valor,
            'status' => $this->status,
            'solicitacaoPagador' => $this->solicitacaoPagador,
            'infoAdicionais' => $this->infoAdicionais
        ];

        if ($filled) {
            $data = array_filter($data, function($row){ return !empty($row); });
        }

        return $data;
    }

}

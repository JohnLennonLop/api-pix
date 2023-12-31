<?php


namespace PixSicredi\Resources;

use PixSicredi\Api;
use PixSicredi\Http\Request;
use PixSicredi\Resources\Filters\PixFilter;


class PixRecebido
{

    public Api $api;
    public Request $request;


    public function __construct(Api $api)
    {
        $this->api = $api;
    }


    public function consult(string $e2eid): PixRecebido
    {
        $this->request = (new Request($this->api))
            ->call($this->api->getUrl('/pix/' . $e2eid));

        return $this;
    }


    public function list(PixFilter $filters): PixRecebido
    {
        $this->request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/pix/'),
                'GET',
                $filters->toArray()
            );

        return $this;
    }


    public function refund(
        string $id,
        string $e2eid,
        float $amount,
        ?string $descricao = NULL,
        ?string $natureza = 'ORIGINAL'
    ): PixRecebido
    {
        $this->request = (new Request($this->api))
            ->call(
                $this->api->getUrl('/pix/' . $e2eid . '/devolucao/' . $id),
                'PUT',
                [
                    'body' => [
                        'valor' => number_format($amount, 2, '.', ''),
                        'natureza' => $natureza,
                        'descricao' => $descricao
                    ]
                ]
            );

        return $this;
    }


    public function consultRefund(string $refundId, string $e2eid): PixRecebido
    {
        $this->request = (new Request($this->api))
            ->call($this->api->getUrl('/pix/' . $e2eid . '/devolucao/' . $refundId));

        return $this;
    }


}

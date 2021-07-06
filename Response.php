<?php


namespace core_fw;


class Response
{
    /**
     * set status code of the response
     * @param int $code
     */
    public function setStatusCode(int $code){
        http_response_code($code);
    }
}
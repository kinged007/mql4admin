<?php
class Mql4Messages
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     *
     */
    public function __destruct()
    {
    }
    
    /**
     * Set friendly columns\' names to order tables\' entries
     */
    public function setOrderingValues()
    {
        $ordering = [
            'id' => 'ID',
            'account' => 'Account Number',
            'server' => 'Server',
            'vps_id' => 'VPS',
            'balance' => 'Balance',
            'equity' => 'Equity',
            'currency' => 'Currency',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at'
        ];

        return $ordering;
    }
}
?>

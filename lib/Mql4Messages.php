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
            'profit' => 'Profit/Loss',
            'currency' => 'Currency',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'timestamp' => 'Last Ping',
            'friendly_name' => 'Friendly Name',
        ];

        return $ordering;
    }
}
?>

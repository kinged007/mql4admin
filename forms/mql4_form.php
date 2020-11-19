<fieldset>
    <div class="form-group">
        <label for="friendly_name">Friendly Name</label>
          <input type="text" name="friendly_name" value="<?php echo htmlspecialchars($edit ? $mql4['friendly_name'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Friendly Name" class="form-control" id = "friendly_name" >
    </div> 
    <div class="form-group">
        <label for="account">Account Number</label>
          <input type="text" name="account" value="<?php echo htmlspecialchars($edit ? $mql4['account'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account Number" class="form-control" disabled="disabled" id = "account" >
          <input type='hidden' name='account' value='<?php echo htmlspecialchars($edit ? $mql4['account'] : '', ENT_QUOTES, 'UTF-8'); ?>'/>
    </div> 
    <div class="form-group">
        <label for="account">Server</label>
          <input type="text" name="server" value="<?php echo htmlspecialchars($edit ? $mql4['server'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Server" class="form-control" disabled="disabled" id = "server" >
          <input type='hidden' name='server' value='<?php echo htmlspecialchars($edit ? $mql4['server'] : '', ENT_QUOTES, 'UTF-8'); ?>'/>
    </div> 
    <div class="form-group">
        <label for="account">VPS</label>
          <input type="text" name="vps_id" value="<?php echo htmlspecialchars($edit ? $mql4['vps_id'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="VPS" class="form-control" disabled="disabled" id = "vps_id" >
    </div> 
    <div class="form-group">
        <label for="account">Company</label>
          <input type="text" name="company" value="<?php echo htmlspecialchars($edit ? $mql4['company'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Company" class="form-control" disabled="disabled" id = "company" >
    </div> 
    <div class="form-group">
        <label for="account">Account Name</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($edit ? $mql4['name'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account Name" class="form-control" disabled="disabled" id = "name" >
    </div> 
    <div class="form-group">
        <label for="account">Trade Allowed
          <input type="text" name="trade_permitted" value="<?php echo htmlspecialchars($edit ? $mql4['trade_permitted'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Trade Allowed" class="form-control" disabled="disabled" id = "trade_permitted" >
        </label>
        <label for="account">EA Allowed
          <input type="text" name="ea_permitted" value="<?php echo htmlspecialchars($edit ? $mql4['ea_permitted'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="EA Allowed" class="form-control" disabled="disabled" id = "ea_permitted" >
        </label>
        <label for="account">DLL Allowed
          <input type="text" name="dll_allowed" value="<?php echo htmlspecialchars($edit ? $mql4['dll_allowed'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="DLL Allowed" class="form-control" disabled="disabled" id = "dll_allowed" >
        </label>
        <label for="account">Account Type
          <input type="text" name="account_type" value="<?php echo htmlspecialchars($edit ? $mql4['account_type'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Account Type" class="form-control" disabled="disabled" id = "account_type" >
        </label>
        <label for="account">Currency
          <input type="text" name="currency" value="<?php echo htmlspecialchars($edit ? $mql4['currency'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Currency" class="form-control" disabled="disabled" id = "currency" >
        </label>
        <label for="account">Leverage
          <input type="text" name="leverage" value="<?php echo htmlspecialchars($edit ? $mql4['leverage'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Leverage" class="form-control" disabled="disabled" id = "leverage" >
        </label>
    </div> 
    <div class="form-group">
        <label for="account">Balance
          <input type="text" name="balance" value="<?php echo htmlspecialchars($edit ? $mql4['balance'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "balance" >
        </label>
        <label for="account">Credit
          <input type="text" name="credit" value="<?php echo htmlspecialchars($edit ? $mql4['credit'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "credit" >
        </label>
        <label for="account">Equity
          <input type="text" name="equity" value="<?php echo htmlspecialchars($edit ? $mql4['equity'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "equity" >
        </label>
        <label for="account">Profit/Loss
          <input type="text" name="profit" value="<?php echo htmlspecialchars($edit ? $mql4['profit'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "profit" >
        </label>
        <label for="account">Open Trades
          <input type="text" name="open_trades" value="<?php echo htmlspecialchars($edit ? $mql4['open_trades'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "open_trades" >
        </label>        
    </div> 
    <div class="form-group">
        <?php /*
            $current_balance = (is_numeric($mql4['balance'])) ? $mql4['balance'] : 0;
            $start_balance_day = (is_numeric($mql4['start_balance_day']))?htmlspecialchars($mql4['start_balance_day']):0;
            $start_balance_week = (is_numeric($mql4['start_balance_week']))?htmlspecialchars($mql4['start_balance_week']):0;
            $start_balance_month = (is_numeric($mql4['start_balance_month']))?htmlspecialchars($mql4['start_balance_month']):0;
            if( $start_balance_day > 0 )
                echo "<span class='badge badge-secondary'>Day</span> ".number_format(($current_balance-$start_balance_day)/$start_balance_day*100,2)."%<br/>"; 
           if( $start_balance_week > 0 )
                echo "<span class='badge badge-primary'>Week</span> ".number_format(($current_balance-$start_balance_week)/$start_balance_week*100,2)."%<br/>"; 
           if( $start_balance_month > 0 )
                echo "<span class='badge badge-success'>Month</span> ".number_format(($current_balance-$start_balance_month)/$start_balance_month*100,2)."%<br/>";                                        
            */      
 
        ?>      
        <label for="account">Balance at start of Day
          <input type="text" name="start_balance_day" value="<?php echo htmlspecialchars($edit ? $mql4['start_balance_day'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" id = "start_balance_day" >
        </label>
        <label for="account">Balance at start of Week
          <input type="text" name="start_balance_week" value="<?php echo htmlspecialchars($edit ? $mql4['start_balance_week'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" id = "start_balance_week" >
        </label>
        <label for="account">Balance at start of Month
          <input type="text" name="start_balance_month" value="<?php echo htmlspecialchars($edit ? $mql4['start_balance_month'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" id = "start_balance_month" >
        </label>
        <label for="account">Balance at start of 3 Months
          <input type="text" name="start_balance_3month" value="<?php echo htmlspecialchars($edit ? $mql4['start_balance_3month'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" id = "start_balance_3month" >
        </label>
        <label for="account">Balance at start of Year
          <input type="text" name="start_balance_year" value="<?php echo htmlspecialchars($edit ? $mql4['start_balance_year'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" id = "start_balance_year" >
        </label>        
    </div> 
    <div class="form-group">
        <label for="account">Margin Used
          <input type="text" name="margin" value="<?php echo htmlspecialchars($edit ? $mql4['margin'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "margin" >
        </label>
        <label for="account">Free Margin
          <input type="text" name="free_margin" value="<?php echo htmlspecialchars($edit ? $mql4['free_margin'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "free_margin" >
        </label>
        <label for="account">Margin Level
          <input type="text" name="margin_level" value="<?php echo htmlspecialchars($edit ? $mql4['margin_level'] : '', ENT_QUOTES, 'UTF-8'); ?> %" placeholder="0" class="form-control" disabled="disabled" id = "margin_level" >
        </label>
    </div> 
    <div class="form-group">
        <label for="account">Stopout Call
          <input type="text" name="stopout_call" value="<?php echo htmlspecialchars($edit ? $mql4['stopout_call'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Stopout" class="form-control" disabled="disabled" id = "stopout_call" >
        </label>
        <label for="account">Stopout Close
          <input type="text" name="stopout_stopout" value="<?php echo htmlspecialchars($edit ? $mql4['stopout_stopout'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Stopout" class="form-control" disabled="disabled" id = "stopout_stopout" >
        </label>
        <label for="account">Stopout Type
          <input type="text" name="stopout_type" value="<?php echo htmlspecialchars($edit ? $mql4['stopout_type'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Stopout" class="form-control" disabled="disabled" id = "stopout_type" >
        </label>
    </div>     
    <div class="form-group">
        <label for="account">Created
          <input type="text" name="created_at" value="<?php echo htmlspecialchars($edit ? $mql4['created_at'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "created_at" >
        </label>
        <label for="account">Last Update
          <input type="text" name="updated_at" value="<?php echo htmlspecialchars($edit ? $mql4['updated_at'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "updated_at" >
        </label>
        <label for="account">Last Connection to Server
          <input type="text" name="timestamp" value="<?php echo htmlspecialchars($edit ? $mql4['timestamp'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="0" class="form-control" disabled="disabled" id = "timestamp" >
        </label>        
    </div> 
    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
    </div>            
</fieldset>

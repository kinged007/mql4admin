<fieldset>
    <!-- Form Name -->
    <legend>Settings</legend>

    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label" >Secret</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input type="text" name="secret" autocomplete="off" placeholder="Secret " class="form-control" required="" autocomplete="off" value="<?php echo ($edit) ? $admin_account['secret'] : ''; ?>">
            </div>
        </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info" name="generate_new_secret">Generate New Secret <span class="fas fa-redo"></span></button>
            </div>
    </div>

    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label"></label>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
</fieldset>
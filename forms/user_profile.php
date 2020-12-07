<fieldset>
    <!-- Form Name -->
    <legend>Update Profile</legend>
    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label">User name</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <input  disabled="disabled" type="text" name="user_name" autocomplete="off" placeholder="user name" class="form-control" value="<?php echo ($edit) ? $admin_account['user_name'] : ''; ?>" autocomplete="off">
            </div>
        </div>
    </div>
    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label" >New Password</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input type="password" name="password" autocomplete="off" placeholder="New password if you want to change it " class="form-control" autocomplete="off">
            </div>
        </div>
    </div>

    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label" >Email</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                <input type="email" name="email" autocomplete="off" placeholder="Email Address" class="form-control" autocomplete="off" value="<?php echo ($edit) ? $admin_account['email'] : ''; ?>">
            </div>
        </div>
    </div>
    <!-- radio checks -->
    <div class="form-group">
        <label class="col-md-4 control-label">User type</label>
        <div class="col-md-4">
            <div class="radio">
                <label>
                    <?php //echo $admin_account['admin_type'] ?>
                    <input disabled="disabled" type="radio" name="admin_type" value="super" required="" <?php echo ($edit && $admin_account['admin_type'] =='super') ? "checked": "" ; ?>/> Super admin
                </label>
            </div>
            <div class="radio">
                <label>
                    <input disabled="disabled" type="radio" name="admin_type" value="admin" required="" <?php echo ($edit && $admin_account['admin_type'] =='admin') ? "checked": "" ; ?>/> Admin
                </label>
            </div>
        </div>
    </div>
    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label"></label>
        <div class="col-md-4">
            <button class="btn btn-warning" data-toggle="modal" data-target="#confirm-update">Save <span class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
    <!-- Update  Confirmation Modal -->
    <div class="modal fade" id="confirm-update" role="dialog">
        <div class="modal-dialog">
                <!-- Modal content -->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Confirm</h4>
                    </div>
                    <div class="modal-body">
                        <p>Please type your current password to confirm changes.</p>
                            <label class="control-label" >Current Password</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input type="password" name="current_password" autocomplete="off" placeholder="Password " class="form-control" required="" autocomplete="off">
                            </div>                        

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning pull-left">Update</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
        </div>
    </div>
    <!-- //Update  Confirmation Modal -->    
</fieldset>
<?= $header ?>
<style>
    .pointer{
        cursor: pointer;
        color: #1e91cf;
    }
</style>
<?= $column_left ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-account" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account" class="form-horizontal">
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-login">
                            <span data-toggle="tooltip" data-html="true" data-trigger="click" title="<?= htmlspecialchars($help_mssync_login) ?>">
                                <?= $text_form_login ?>
                            </span></label>
                        <div class="col-sm-10">
                            <input type="text" name="mssync_login" value="<?php echo $mssync_login ?>" placeholder="<?php echo $text_form_login ?>" id="input-login" class="form-control" />
                            <?php if ($error_login) { ?>
                            <div class="text-danger"><?= $error_login ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-password">
                            <span data-toggle="tooltip" data-html="true" data-trigger="click" title="<?= htmlspecialchars($help_mssync_password) ?>">
                                <?= $text_form_password ?>
                            </span></label>
                        <div class="col-sm-10">
                            <input type="password" name="mssync_password" value="<?php echo $mssync_password ?>" placeholder="<?php echo $text_form_password ?>" id="input-password" class="form-control" />
                            <?php if ($error_password) { ?>
                            <div class="text-danger"><?= $error_password ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-organization-uuid">
                            <span data-toggle="tooltip" data-html="true" data-trigger="click" title="<?= htmlspecialchars($help_mssync_organization) ?>">
                                <?= $text_form_organization ?>
                            </span>
                        </label>
                        <div class="col-sm-10">
                            <?php
                            if($print_organizations){
                            ?>
                            <select name="mssync_organization_uuid" class="form-control">
                                <?php
                                for($i = 0; $i < $count_organizations; $i++){
                                ?>
                                <option value="<?= $mssync_organization_uuid_list[$i]['id'] ?>"><?= $mssync_organization_uuid_list[$i]["name"] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            <?php
                            } else {
                            ?>
                            <input type="text" name="mssync_organization_uuid" class="form-control" 
                                   value="<?= $mssync_organization_uuid ?>">
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-store-uuid">
                            <span data-toggle="tooltip" data-html="true" data-trigger="click" title="<?= htmlspecialchars($help_mssync_store) ?>">
                                <?= $text_form_store ?>
                            </span>
                        </label>
                        <div class="col-sm-10">
                            <?php
                            if($print_stores){
                            ?>
                            <select name="mssync_store_uuid" class="form-control <?= $store_uuid_required ?>">
                                <?php
                                for($i = 0; $i < $count_stores; $i++){
                                ?>
                                <option value="<?= $mssync_store_uuid_list[$i]['id'] ?>"><?= $mssync_store_uuid_list[$i]["name"] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            <?php
                            } else {
                            ?>
                            <input type="text" name="mssync_store_uuid" class="form-control" 
                                   value="<?= $mssync_store_uuid ?>">
                            <?php
                            }
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-state-uuid">
                            <span data-toggle="tooltip" data-html="true" data-trigger="click" title="<?= htmlspecialchars($help_mssync_state) ?>">
                                <?= $text_form_state ?>
                            </span>
                        </label>
                        <div class="col-sm-10">
                            <?php
                            if($print_states){
                            ?>
                            <select name="mssync_new_order_state_uuid" class="form-control <?= $state_uuid_required ?>">
                                <?php
                                for($i = 0; $i < $mssync_states_uuid_list_count; $i++){
                                ?>
                                <option value="<?= $mssync_states_uuid_list[$i]['id'] ?>"><?= $mssync_states_uuid_list[$i]["name"] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            <?php
                            } else {
                            ?>
                            <input type="text" name="mssync_new_order_state_uuid" class="form-control" 
                                   value="<?= $mssync_new_order_state_uuid ?>">
                            <?php
                            }
                            ?>
                        </div>
                    </div>

                    <!--<div class="form-group">
                        <label class="col-sm-2 control-label" for="input-vat">
                            <?= $text_form_vat_included ?>
                        </label>
                        <div class="col-sm-10">-->
                    <input type="hidden" name="mssync_vat" id="input-vat" class="form-control" <?= $mssync_vat_in_sel ?> />
                           <!--</div>
                       </div>

                    <div class="form-group">  
                        <label class="col-sm-2 control-label" for="input-show-zero-qty"><?= $text_form_show_zero_qnt_prod ?></label>
                        <div class="col-sm-10">-->
                           <input type="hidden" name="mssync_show_zero_qty_prod" value="1" id="input-show-zero-qty" />
                    <!--</div>
                </div>-->
                    <div class="form-group">  
                        <label class="col-sm-2 control-label"><?= $text_form_sync_product ?></label>
                        <div class="col-sm-10">
                            <a id="sync_products" href="<?= $sync_products_href ?>"><?= $text_form_sync ?></a>
                        </div>
                    </div>
                    <div class="form-group">  
                        <label class="col-sm-2 control-label"><?= $text_form_sync_assortment ?></label>
                        <div class="col-sm-10">
                            <a id="sync_assortment" href="<?= $sync_assortment_href ?>"><?= $text_form_sync ?></a>
                        </div>
                    </div>

                    <div class="form-group">  
                        <label class="col-sm-2 control-label"><?= $text_form_add_products ?></label>
                        <div class="col-sm-10">
                            <a href="<?= $add_products_href ?>">Выгрузить</a>
                        </div>
                    </div>

                    <div class="form-group">  
                        <label class="col-sm-2 control-label" for="input-status"><?= $entry_status ?></label>
                        <div class="col-sm-10">
                            <select name="mssync_status" id="input-status" class="form-control">
                                <?php if ($mssync_status) { ?>
                                <option value="1" selected="selected"><?= $text_enabled ?></option>
                                <option value="0"><?= $text_disabled ?></option>
                                <?php } else { ?>
                                <option value="1"><?= $text_enabled ?></option>
                                <option value="0" selected="selected"><?= $text_disabled ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $footer ?>
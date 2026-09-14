<div class="mx-auto" style="max-width: 800px;">
    <div class="panel mb-0 rounded-0">
        <div class="panel-hdr">
            <h2>
                {if $edit}
                    {$task['title']}
                {else}
                    {$_L['Add New']}
                {/if}
            </h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <form id="ib-modal-form" method="post">

                    <div class="mb-3">
                        <label for="title">{$_L['Subject']}</label>
                        <input type="text" class="form-control" id="title" name="title" value="{$task['title']}">
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="start_date">{$_L['Start Date']}</label>
                            <input type="text" class="form-control" id="start_date" data-date-format="yyyy-mm-dd" data-auto-close="true" name="start_date" value="{$task['started']}">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="due_date">{$_L['Due Date']}</label>
                            <input type="text" class="form-control" id="due_date" data-date-format="yyyy-mm-dd" data-auto-close="true" name="due_date" value="{$task['due_date']}">
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class='mb-3 col-md-6'>
                            <label for="cid">{__('Customer')}</label>

                            <select id="cid" name="cid" class="form-select">
                                <option value="">{$_L['Select Contact']}...</option>
                                {foreach $c as $cs}
                                    <option value="{$cs->id}"
                                            {if $task['cid'] eq ($cs['id'])}selected="selected" {/if}>{$cs->account} {if $cs->email neq ''}- {$cs->email}{/if}</option>
                                {/foreach}

                            </select>

                        </div>
                        <div class='mb-3 col-md-6'>
                            <label for="cid">{__('Assigned To')}</label>

                            <select id="aid" name="aid" class="form-select">
                                <option value="">{__('User')}...</option>
                                {foreach $admin_users as $admin_user}
                                    <option value="{$admin_user->id}" {if !empty($task->aid) && $task->aid eq $admin_user->id}selected="selected" {/if}>{$admin_user->fullname}</option>
                                {/foreach}
                            </select>

                        </div>
                    </div>




                    <div class="mb-3">
                        <label for="subject">{$_L['Description']}</label>
                        <textarea class="form-control" id="description" name="description" rows="10">{$task['description']}</textarea>
                    </div>



                    <input type="hidden" id="project_id" name="project_id" value="{$project_id}">
                    <input type="hidden" id="status" name="status" value="{$task['status']}">
                    <input type="hidden" id="task_id" name="task_id" value="{$task['id']}">
                    <input type="hidden" name="project_id" value="{$project_id}">

                    <button type="submit" class="btn btn-primary modal_submit">{$_L['Save']}</button>

                </form>
            </div>
        </div>
    </div>
</div>


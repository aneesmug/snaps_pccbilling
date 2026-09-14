{extends file="$layouts_admin"}

{block name="content"}

<div class="row">
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-container">
                <div class="panel-content">
                    <h4>Legacy SQL File Import</h4>
                    <hr>
                    <p class="text-muted">
                        Upload a .sql export file from old iBilling. The importer will only process INSERT/REPLACE data for the following tables:
                    </p>

                    <p class="text-muted">
                        The importer maps old dump columns to the current schema automatically, ignores unknown legacy columns, and fills required new fields when the new schema contains extra columns.
                    </p>

                    <form method="post" action="{$_url}util/sql-import-check-schema" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="sql_file">SQL File</label>
                            <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql" required>
                        </div>

                        <div class="mb-3">
                            <label>Tables To Import</label>
                            <div style="max-height: 260px; overflow-y: auto; border: 1px solid #e5e5e5; padding: 12px; border-radius: 4px;">
                                {foreach $legacy_import_tables as $table}
                                    <div class="checkbox" style="margin-bottom: 8px;">
                                        <label>
                                            <input type="checkbox" name="import_tables[]" value="{$table}" checked>
                                            {$table}
                                        </label>
                                    </div>
                                {/foreach}
                            </div>
                        </div>

                        <div class="checkbox" style="margin-bottom: 16px;">
                            <label>
                                <input type="checkbox" name="truncate_existing" value="yes" checked>
                                Truncate existing rows in target migration tables before import
                            </label>
                        </div>

                        <div class="checkbox" style="margin-bottom: 16px;">
                            <label>
                                <input type="checkbox" name="dry_run" value="yes">
                                Dry run only: parse and validate without writing to database
                            </label>
                        </div>

                        <div class="checkbox" style="margin-bottom: 16px;">
                            <label>
                                <input type="checkbox" name="skip_duplicates" value="yes" checked>
                                Skip duplicate rows when matching data already exists in current system
                            </label>
                        </div>

                        <div class="checkbox" style="margin-bottom: 16px;">
                            <label>
                                <input type="checkbox" name="allow_suspect_encoding" value="yes">
                                Allow import even if UTF-8 safety validator detects possible Arabic text corruption (not recommended)
                            </label>
                        </div>

                        <p class="text-muted" style="margin-top: -8px; margin-bottom: 12px;">
                            Step 1: run Schema Check to validate selected SQL file against live schema. Step 2: Insert Data button appears only when schema check PASS.
                        </p>

                        <button type="submit" class="btn btn-info">Schema Check List</button>
                        <a href="{$_url}util/tools" class="btn btn-default">Back To Utility Tools</a>
                    </form>

                    {if $staged_import_token}
                        <hr>
                        <div class="alert alert-success" style="margin-bottom: 10px;">
                            Ready to import staged file: <strong>{$staged_file_name}</strong>
                        </div>
                        <form method="post" action="{$_url}util/sql-import-run">
                            <input type="hidden" name="staged_import_token" value="{$staged_import_token}">

                            <div class="checkbox" style="margin-bottom: 12px;">
                                <label>
                                    <input type="checkbox" name="truncate_existing" value="yes" {if $staged_options.truncate_existing}checked{/if}>
                                    Truncate existing rows in target migration tables before import
                                </label>
                            </div>

                            <div class="checkbox" style="margin-bottom: 12px;">
                                <label>
                                    <input type="checkbox" name="dry_run" value="yes" {if $staged_options.dry_run}checked{/if}>
                                    Dry run only: parse and validate without writing to database
                                </label>
                            </div>

                            <div class="checkbox" style="margin-bottom: 12px;">
                                <label>
                                    <input type="checkbox" name="skip_duplicates" value="yes" {if $staged_options.skip_duplicates}checked{/if}>
                                    Skip duplicate rows when matching data already exists in current system
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">Insert Data</button>
                        </form>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel">
            <div class="panel-container">
                <div class="panel-content">
                    <h4>Schema Check Report</h4>
                    <hr>

                    {if $schema_check_status}
                        <div class="alert {if $schema_check_status.overall == 'PASS'}alert-success{else}alert-danger{/if}" style="margin-bottom: 12px;">
                            <strong>Schema Check: {$schema_check_status.overall}</strong>
                            <div style="margin-top: 8px; font-family: Menlo, Consolas, monospace; font-size: 12px;">
                                {foreach $schema_check_status.checks as $check}
                                    <div>[{if $check.ok}PASS{else}FAIL{/if}] {$check.label} - {$check.detail}</div>
                                {/foreach}
                            </div>
                        </div>
                    {/if}

                    <textarea class="form-control" rows="10" readonly>{if $schema_check_report}{$schema_check_report}{else}No schema check has been run yet.{/if}</textarea>

                    <h4 style="margin-top: 16px;">Import Report</h4>
                    <hr>

                    {if $import_status}
                        <div class="alert {if $import_status.overall == 'PASS'}alert-success{else}alert-danger{/if}" style="margin-bottom: 12px;">
                            <strong>Import Status: {$import_status.overall}</strong>
                            <div style="margin-top: 8px; font-family: Menlo, Consolas, monospace; font-size: 12px;">
                                {foreach $import_status.checks as $check}
                                    <div>[{if $check.ok}PASS{else}FAIL{/if}] {$check.label} - {$check.detail}</div>
                                {/foreach}
                            </div>
                        </div>
                    {/if}

                    <textarea class="form-control" rows="12" readonly>{if $import_report}{$import_report}{else}No import has been run yet.{/if}</textarea>

                    <h4 style="margin-top: 16px;">Post-Import Verification SQL</h4>
                    <hr>
                    <p class="text-muted" style="margin-top: -8px;">
                        Run this checklist in your target database after import to verify counts and sample record integrity.
                    </p>
                    {if $verification_sql}
                        <div style="margin-bottom: 10px;">
                            <a href="{$_url}util/sql-import-export-checklist" class="btn btn-success btn-sm">
                                One-Click Export Checklist (.sql)
                            </a>
                        </div>
                    {/if}
                    <textarea class="form-control" rows="18" readonly>{if $verification_sql}{$verification_sql}{else}Run a dry run or import first to auto-generate verification SQL checklist.{/if}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

{/block}

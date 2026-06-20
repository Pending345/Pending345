{extends file="layout.tpl"}

{block name="content"}
    {if isset($error)}
        <div class="alert alert-danger">
            {$error}
        </div>
    {/if}
    <div class="card">
        <div class="card-header">
            <h1>Welcome to Kalender</h1>
        </div>
        <div class="card-body">
            <figure>
                <p>"Een introductietekst over de kalender"</p>
            </figure>
        </div>
    </div>
{/block}
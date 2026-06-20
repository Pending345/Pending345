{extends file="layout.tpl"}
{block name="content"}

    <div class="container py-5">
        {if isset($error)}
            <div class="alert alert-danger">
                {$error}
            </div>
        {/if}
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Log In</div>
            <div class="card-body">
                <form action="index.php?page=login" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>
            </div>
        </div>
        <div class="alert alert-info mt-3">
            Don’t have an account?
            <a href="index.php?page=signupForm">Create one here</a>.
        </div>
    </div>
{/block}

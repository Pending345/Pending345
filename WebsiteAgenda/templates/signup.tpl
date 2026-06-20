{extends file="layout.tpl"}
{block name="content"}

    <div class="container py-5">

        {if isset($error)}
            <div class="alert alert-danger">
                {$error}
            </div>
        {/if}
        <div class="card shadow-sm">
            <div class="card-header fw-bold">Sign Up</div>
            <div class="card-body">
                <form action="index.php?page=signup" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="text" class="form-control" name="email" required>
                        <div class="form-text">We'll never share your email with anyone else.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <div class="form-text">Password must have at least 6 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Repeat password</label>
                        <input type="password" class="form-control" name="repeatPassword" required minlength="6">
                        <div class="form-text">Passwords must match</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>
            </div>
        </div>
        <div class="alert alert-info mt-3">
            Already have an account? <a href="index.php?page=loginForm">Log in instead</a>.
        </div>
    </div>
{/block}

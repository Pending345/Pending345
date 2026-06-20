{extends file="layout.tpl"}
{block name="content"}
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Calendar</h1>
            <div class="btn-group">
                <a class="btn btn-outline-primary" href="index.php?page=calendarDay&time={$previousDay}">Previous</a>
                <a class="btn btn-outline-primary" href="index.php?page=calendarDay&time={$nextDay}">Next</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-bold text-center py-3">{$date}</div>
            <div class="card-body">
                {$calendarHTML}
            </div>
        </div>
        {include file="eventForm.tpl"}
    </div>
{/block}
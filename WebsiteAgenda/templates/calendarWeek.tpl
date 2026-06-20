{extends file="layout.tpl"}
{block name="content"}
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Calendar</h1>
            <div class="btn-group">
                <a class="btn btn-outline-primary" href="index.php?page=calendarWeek&time={$previousWeek}">Previous</a>
                <a class="btn btn-outline-primary" href="index.php?page=calendarWeek&time={$nextWeek}">Next</a>
            </div>
        </div>

        <div class="d-flex gap-3">
            <div class="card shadow-sm flex-fill">
                <div class="card-header fw-bold text-center py-3">{$month}</div>
                <div class="card-body small">
                    <div class="d-flex gap-3">
                        {$calendarHTML}
                    </div>
                </div>
            </div>
        </div>
        {include file="eventForm.tpl"}
    </div>
{/block}

{extends file="layout.tpl"}
{block name="content"}
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Calendar</h1>
            <div class="btn-group">
                <a class="btn btn-outline-primary" href="index.php?page=calendarMonth&time={$previousMonth}">Previous</a>
                <a class="btn btn-outline-primary" href="index.php?page=calendarMonth&time={$nextMonth}">Next</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-bold text-center py-3">{$date}</div>
            <div class="card-body p-0">
                <table class="table table-bordered m-0 text-center align-middle">
                    <thead>
                    <tr>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                        <th>Sunday</th>
                    </tr>
                    </thead>
                    <tbody>
                        {$calendarHTML}
                    </tbody>
                </table>
            </div>
        </div>
        {include file="eventForm.tpl"}
    </div>
{/block}
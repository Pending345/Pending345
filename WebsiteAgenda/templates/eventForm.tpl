<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
    Add Event
</button>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eventModalLabel">Add Event</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=addEvent" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="mb-3 row">
                        <div class="col">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" name="time" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col">
                            <label for="color" class="form-label">Choose a color:</label>
                            <input type="color" id="color" class="form-control form-control-color: w-100 p-0" name="color" value="{$color|default:'#ff0000'}">
                        </div>
                        <div class="col">
                            <label for="dis" class="form-label">Temp</label>
                            <input type="file" class="form-control" id="dis" name="dis">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="repeating" name="repeating" data-bs-toggle="collapse" data-bs-target="#extraFields">
                            <label class="form-check-label" for="repeating">
                                Enable repeating events
                            </label>
                        </div>
                        <div class="collapse" id="extraFields">
                            <div class="card card-body">
                                <div class="mb-3 row">
                                    <div class="col">
                                        <label for="repeatType" class="form-label">Repeat every</label>
                                        <select id="repeatType" name="repeatType" class="form-select" required>
                                            <option value="week" name="week" selected>Week</option>
                                            <option value="month" name="month">Month</option>
                                            <option value="year" name="year">Year</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="endDate" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="endDate" name="endDate" value="2100-01-01" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="returnUrl" value="{$returnTo}">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
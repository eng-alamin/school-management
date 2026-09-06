<div> 
    <div class="card"> 

        <!-- Floating Header -->
        <div class="mat-card-header header-primary-gradient">
            <h5>
                Edit Homework
            </h5>
            <p>Update existing homework record</p>
        </div>

        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Class & Subject
            </div>

            <div class="row g-4">

                <!-- Class -->
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class <span class="req">*</span></label>
                        <select wire:model.live="class_id" class="form-select">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Section -->
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Section @if($classHasSection)<span class="req">*</span>@endif</label>
                        <select wire:model.live="section_id" class="form-select"
                            {{ (!$class_id || !$classHasSection || empty($availableSections)) ? 'disabled' : '' }}>
                            @if(!$class_id)
                                <option value="">Select Class First</option>
                            @elseif(!$classHasSection)
                                <option value="">N/A — this class has no sections</option>
                            @else
                                <option value="">Select Section</option>
                                @if(count($availableSections) > 1)
                                    <option value="all">All Section</option>
                                @endif
                                @foreach ($availableSections as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @error('section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Subject -->
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Subject <span class="req">*</span></label>
                        <select wire:model="subject_id" class="form-select"
                            {{ empty($availableSubjects) ? 'disabled' : '' }}>
                            <option value="">{{ !$class_id ? 'Select Class First' : 'Select Subject' }}</option>
                            @foreach ($availableSubjects as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('subject_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="row g-4 mt-0">

                <!-- Teacher -->
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Teacher</label>
                        <select wire:model="teacher_id" class="form-select">
                            <option value="">Select Teacher (Optional)</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">edit_note</span> Homework Details
            </div>

            <div class="row g-4">

                <!-- Homework Date -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Homework Date <span class="req">*</span></label>
                        <input type="date" wire:model="homework_date" class="form-control"
                            data-dp-value="{{ $homework_date }}"
                            onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('homework_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Submission Date -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Submission Date <span class="req">*</span></label>
                        <input type="date" wire:model="submission_date" class="form-control"
                            data-dp-value="{{ $submission_date }}"
                            onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('submission_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Title -->
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Title <span class="req">*</span></label>
                        <input type="text" wire:model="title" class="form-control"
                            placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Description <span class="req">*</span></label>
                        <textarea wire:model="description" class="form-control" style="min-height:120px"
                            placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Attachment -->
                <div class="col-12">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Attachment File
                    </label>
                    <div class="photo-upload-box">
                        <span class="material-icons-round">attach_file</span>
                        <span class="lbl">Click to upload new attachment</span>
                        <small style="color:#bbb;font-size:.7rem">PDF, DOC, JPG, PNG up to 10MB — leave empty to keep existing</small>
                        <input type="file" wire:model="attachment">
                    </div>
                    @error('attachment') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-select">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Publish Later + Send SMS -->
                <div class="col-md-6 d-flex align-items-center gap-4" style="padding-top:8px">
                    <div class="form-check mt-2">
                        <input wire:model.live="published_later" class="form-check-input"
                            type="checkbox" id="publishedLater">
                        <label class="form-check-label" for="publishedLater">Publish Later</label>
                    </div>

                    <div class="form-check mt-2">
                        <input wire:model="send_sms" class="form-check-input"
                            type="checkbox" id="sendSms">
                        <label class="form-check-label" for="sendSms">Send Notification SMS</label>
                    </div>
                </div>

                <!-- Schedule Date (conditional) -->
                @if($published_later)
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Schedule Date <span class="req">*</span></label>
                        <input type="datetime-local" wire:model="schedule_date" class="form-control"
                            onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('schedule_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                @endif

            </div>
        </div>

        <!-- Form Footer -->
        <div class="form-footer">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <span>
                    <span class="material-icons-round" style="font-size:16px">arrow_back</span>
                    <span>Back</span>
                </span>
            </button>

            <button class="btn btn-primary" type="button"
                    wire:click="update"
                    wire:loading.attr="disabled"
                    wire:target="update">

                <span wire:loading.remove wire:target="update">
                    <span class="material-icons-round">save</span>
                    <span>Update</span>
                </span>

                <span wire:loading wire:target="update">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                    <span>Updating...</span>
                </span>
            </button>
        </div>

    </div>
</div>
<div>
    <div class="card border-0 bg-transparent">
        <div class="container-xl mt-4">

            @include('livewire.admin.student.student-navbar', ['student' => $student])

            <!-- Enrollment History -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <span class="material-icons-round text-primary">history</span>
                    <span class="fw-semibold">Enrollment History</span>
                </div>
                <div class="card-body p-0">

                    @if($enrollments->isEmpty())
                        <div class="text-muted text-center py-5">
                            No enrollment records found for this student.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Group</th>
                                        <th>Roll No</th>
                                        <th>Status</th>
                                        <th>Due Carried Forward</th>
                                        <th>Recorded On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->class->name ?? '—' }}</td>
                                            <td>{{ $enrollment->section->name ?? '—' }}</td>
                                            <td>{{ $enrollment->group->name ?? '—' }}</td>
                                            <td>{{ $enrollment->roll_no ?? '—' }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($enrollment->status) {
                                                        'running'  => 'bg-success',
                                                        'promoted' => 'bg-primary',
                                                        'left'     => 'bg-warning text-dark',
                                                        'alumni'   => 'bg-secondary',
                                                        default    => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($enrollment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($enrollment->carry_forward_due)
                                                    <span class="badge bg-danger">Yes</span>
                                                @else
                                                    <span class="badge bg-light text-dark border">No</span>
                                                @endif
                                            </td>
                                            <td>{{ $enrollment->created_at?->format('d M Y') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 data-en="My Grievances" data-bn="আমার অভিযোগ সমূহ">My Grievances</h4>
        <a href="{{ route('student.grievances.create') }}" class="btn btn-primary btn-sm" data-en="+ New Grievance" data-bn="+ নতুন অভিযোগ">+ New Grievance</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th data-en="Subject" data-bn="বিষয়">Subject</th>
                    <th data-en="Category" data-bn="বিভাগ">Category</th>
                    <th data-en="Status" data-bn="অবস্থা">Status</th>
                    <th data-en="Submitted" data-bn="জমা দেওয়া হয়েছে">Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grievances as $grievance)
                    <tr>
                        <td>{{ $grievance->subject }}</td>
                        <td>{{ $grievance->category }}</td>
                        <td>
                            <span class="badge {{ $grievance->status === 'resolved' ? 'bg-success' : ($grievance->status === 'rejected' ? 'bg-secondary' : 'bg-warning text-dark') }}">
                                {{ $grievance->statusLabel() }}
                            </span>
                        </td>
                        <td>{{ $grievance->created_at->format('d M, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4" data-en="No grievances submitted yet." data-bn="এখনো কোনো অভিযোগ জমা দেওয়া হয়নি।">No grievances submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $grievances->links() }}
</div>
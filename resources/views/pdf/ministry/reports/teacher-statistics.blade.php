@extends('pdf.ministry.reports.partials.layout', ['title' => 'Teacher Statistics Report'])

@section('content')
    <div class="summary-cards">
        <div class="summary-card"><div class="value">{{ $data['total_teachers'] }}</div>Total Teachers</div>
    </div>

    <h3>Subject-wise Teacher Distribution</h3>
    <table>
        <thead><tr><th>Subject</th><th>Teacher Count</th></tr></thead>
        <tbody>
            @foreach ($data['subject_distribution'] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->teacher_count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Teacher-Student Ratio per Institution</h3>
    <table>
        <thead><tr><th>Institution</th><th>Students</th><th>Teachers</th><th>Ratio</th></tr></thead>
        <tbody>
            @foreach ($data['ratios'] as $row)
                <tr class="{{ $row->flagged ? 'flagged' : '' }}">
                    <td>{{ $row->institution_name }}</td>
                    <td>{{ $row->student_count }}</td>
                    <td>{{ $row->teacher_count }}</td>
                    <td>{{ $row->ratio ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
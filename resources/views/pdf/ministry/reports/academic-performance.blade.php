@extends('pdf.ministry.reports.partials.layout', ['title' => 'Academic Performance Report'])

@section('content')
    <div class="summary-cards">
        <div class="summary-card"><div class="value">{{ $data['published_exams'] }}</div>Published Exams</div>
        <div class="summary-card"><div class="value">{{ $data['results_evaluated'] }}</div>Results Evaluated</div>
        <div class="summary-card"><div class="value">{{ $data['pass_rate'] }}%</div>Pass Rate</div>
        <div class="summary-card"><div class="value">{{ $data['avg_gpa'] }}</div>Avg GPA</div>
    </div>

    <h3>Result Status Breakdown</h3>
    <table>
        <thead><tr><th>Status</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($data['status_breakdown'] as $row)
                <tr><td>{{ ucfirst($row->result) }}</td><td>{{ $row->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Division-wise Performance</h3>
    <table>
        <thead><tr><th>Division</th><th>Results</th><th>Pass Rate</th><th>Avg GPA</th></tr></thead>
        <tbody>
            @foreach ($data['division_wise'] as $row)
                <tr class="{{ $row->flagged ? 'flagged' : '' }}">
                    <td>{{ $row->division }}</td>
                    <td>{{ $row->total_results }}</td>
                    <td>{{ $row->pass_rate }}%</td>
                    <td>{{ round($row->avg_gpa, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Institution-wise Performance</h3>
    <table>
        <thead><tr><th>Institution</th><th>Division</th><th>Results</th><th>Pass Rate</th><th>Avg GPA</th></tr></thead>
        <tbody>
            @foreach ($data['institution_wise'] as $row)
                <tr class="{{ $row->flagged ? 'flagged' : '' }}">
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->division }}</td>
                    <td>{{ $row->total_results }}</td>
                    <td>{{ $row->pass_rate }}%</td>
                    <td>{{ round($row->avg_gpa, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@extends('pdf.ministry.reports.partials.layout', ['title' => 'Student Statistics Report'])

@section('content')
    <div class="summary-cards">
        <div class="summary-card"><div class="value">{{ $data['total'] }}</div>Total Students</div>
    </div>

    <h3>Gender Breakdown</h3>
    <table>
        <thead><tr><th>Gender</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($data['gender_breakdown'] as $row)
                <tr><td>{{ ucfirst($row->gender) }}</td><td>{{ $row->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Class-wise Breakdown</h3>
    <table>
        <thead><tr><th>Class</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($data['class_breakdown'] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Division-wise Breakdown</h3>
    <table>
        <thead><tr><th>Division</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($data['division_breakdown'] as $row)
                <tr><td>{{ $row->division }}</td><td>{{ $row->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endsection
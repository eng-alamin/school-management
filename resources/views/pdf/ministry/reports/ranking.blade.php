@extends('pdf.ministry.reports.partials.layout', ['title' => 'Institution Ranking Report (Provisional — Academic Only)'])

@section('content')
    <table>
        <thead>
            <tr>
                <th>Rank</th><th>Institution</th><th>Division</th>
                <th>GPA Score</th><th>Academic Score</th><th>Final Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $row)
                <tr>
                    <td>{{ $row->rank }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->division }}</td>
                    <td>{{ $row->gpa_score }}</td>
                    <td>{{ $row->academic_score }}</td>
                    <td>{{ $row->final_score }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
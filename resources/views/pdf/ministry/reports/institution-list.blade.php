@extends('pdf.ministry.reports.partials.layout', ['title' => 'Institution List Report'])

@section('content')
    <table>
        <thead>
            <tr>
                <th>Name</th><th>EIIN</th><th>Division</th><th>District</th>
                <th>Active</th><th>Verification</th><th>Verified At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->eiin ?? '-' }}</td>
                    <td>{{ $row->division }}</td>
                    <td>{{ $row->district }}</td>
                    <td>{{ $row->status ? 'Active' : 'Inactive' }}</td>
                    <td>{{ ucfirst($row->verification_status) }}</td>
                    <td>{{ $row->verified_at ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
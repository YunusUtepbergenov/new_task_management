<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Task Name</th>
            <th>Category</th>
            <th>Deadline</th>
            <th>Employee</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tasks as $index => $task)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $task->name }}</td>
                <td>{{ $task->score->name ?? '—' }}</td>
                <td>{{ $task->deadline }}</td>
                <td>{{ $task->user->name ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

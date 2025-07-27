@extends('layouts.app')

@section('title', 'Items Management')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-list me-2"></i>Управление записями
            <small class="text-muted">({{ $items->total() }} шт.)</small>
        </h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Google Sheets Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fab fa-google me-2"></i>Конфигурация Google таблицы
                </h5>
            </div>
            <div class="card-body">
                @if ($googleClientEmail)
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-envelope me-1"></i>Email Аккаунта Google Сервиса:
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="googleClientEmail"
                            value="{{ $googleClientEmail }}" readonly>
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="copyToClipboard('googleClientEmail')"
                                id="copyBtn">
                            <i class="fas fa-copy me-1"></i>Копировать
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Используйте этот email для предоставления доступа к Google таблице. Добавьте его в пользователей с доступом.
                    </small>
                </div>
                <hr>

                @endif
                <script>
                function copyToClipboard(elementId) {
                    const element = document.getElementById(elementId);
                    const button = document.getElementById('copyBtn');
                    const originalContent = button.innerHTML;

                    // Select and copy the text
                    element.select();
                    element.setSelectionRange(0, 99999); // For mobile devices

                    navigator.clipboard.writeText(element.value).then(function() {
                        // Success feedback
                        button.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                        button.classList.remove('btn-outline-secondary');
                        button.classList.add('btn-success');

                        // Reset button after 2 seconds
                        setTimeout(function() {
                            button.innerHTML = originalContent;
                            button.classList.remove('btn-success');
                            button.classList.add('btn-outline-secondary');
                        }, 2000);
                    }).catch(function(err) {
                        // Fallback for older browsers
                        document.execCommand('copy');
                        button.innerHTML = '<i class="fas fa-check me-1"></i>Скопировано!';
                        button.classList.remove('btn-outline-secondary');
                        button.classList.add('btn-success');

                        setTimeout(function() {
                            button.innerHTML = originalContent;
                            button.classList.remove('btn-success');
                            button.classList.add('btn-outline-secondary');
                        }, 2000);
                    });
                }
                </script>

                <form action="{{ route('google.sheet.url') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-link"></i>
                        </span>
                        <input type="url" name="google_sheet_url" class="form-control"
                               placeholder="https://docs.google.com/spreadsheets/d/..."
                               value="{{ $googleSheetUrl }}" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-save me-1"></i>Обновить ссылку
                        </button>
                    </div>
                </form>

                @if ($googleSheetUrl)
                    <div class="d-flex gap-2">
                        <form action="{{ route('test.google.connection') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plug me-1"></i>Протестировать подключение
                            </button>
                        </form>

                        <a href="{{ $googleSheetUrl }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Открыть таблицу
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Действия
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="{{ route('items.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Добавить новую запись
                            </a>

                            <form action="{{ route('items.generate') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-info w-100"
                                        >
                                    <i class="fas fa-magic me-2"></i>Сгенерировать 1000 записей
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="/fetch" class="btn btn-secondary" target="_blank">
                                <i class="fas fa-download me-2"></i>Получить данные из Google таблицы
                            </a>

                            <form action="{{ route('items.clear') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Это действие удалит все данные безвозвратно. Вы уверены?')">
                                    <i class="fas fa-trash me-2"></i>Очистить все записи
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>Список записей
                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">
                        Allowed: {{ \App\Models\Item::allowed()->count() }}
                    </span>
                    <span class="badge bg-danger">
                        Prohibited: {{ \App\Models\Item::prohibited()->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                @if ($items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Описание</th>
                                    <th>Статус</th>
                                    <th>Создано</th>
                                    <th>Обновлено</th>
                                    <th width="200">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td><strong>{{ $item->id }}</strong></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ Str::limit($item->description, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $item->status === 'Allowed' ? 'success' : 'danger' }}">
                                                <i class="fas fa-{{ $item->status === 'Allowed' ? 'check' : 'times' }} me-1"></i>
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $item->updated_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('items.show', $item) }}" class="btn btn-outline-info rounded" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-warning mx-2 rounded" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete"
                                                            onclick="return confirm('Удалить эту запись?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        {{ $items->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No items found</h5>
                        <p class="text-muted">Start by creating a new item or generating sample data.</p>
                        <a href="{{ route('items.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Item
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

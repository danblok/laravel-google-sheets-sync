@extends('layouts.app')

@section('title', 'Просмотр записи #'.$item->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-eye me-2"></i>Детали записи #{{ $item->id }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-tag me-1"></i>Название
                        </h6>
                        <p class="fw-bold">{{ $item->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-toggle-on me-1"></i>Статус
                        </h6>
                        <span class="badge bg-{{ $item->status === 'Allowed' ? 'success' : 'danger' }} fs-6">
                            <i class="fas fa-{{ $item->status === 'Allowed' ? 'check' : 'times' }} me-1"></i>
                            {{ $item->status }}
                        </span>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-align-left me-1"></i>Описание
                        </h6>
                        <p>{{ $item->description ?: 'No description provided.' }}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-calendar-plus me-1"></i>Создано
                        </h6>
                        <p>{{ $item->created_at->format('F d, Y \a\t H:i') }}</p>
                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-calendar-edit me-1"></i>Обновлено
                        </h6>
                        <p>{{ $item->updated_at->format('F d, Y \a\t H:i') }}</p>
                        <small class="text-muted">{{ $item->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>К списку
                    </a>
                    <div>
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Обновить
                        </a>
                        <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Вы уверены, что хотите удалить эту запись?')">
                                <i class="fas fa-trash me-2"></i>Удалить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

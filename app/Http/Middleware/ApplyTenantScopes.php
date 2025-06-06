<?php

namespace App\Http\Middleware;

use App\Models\Label;
use App\Models\Task;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Label::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant())
        );
        Task::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant())
        );
        
        return $next($request);
    }
}

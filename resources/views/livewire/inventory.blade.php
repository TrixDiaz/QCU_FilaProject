<div>
{{ $this->table }}
{{--    <style>--}}
{{--        .space { padding-block: 8px }--}}
{{--        .building-grid, .classroom-grid, .asset-grid {--}}
{{--            display: grid;--}}
{{--            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));--}}
{{--            gap: 20px;--}}
{{--            list-style-type: none;--}}
{{--            padding: 0;--}}
{{--        }--}}

{{--        .building-item, .classroom-item, .asset-item {--}}
{{--            padding: 20px;--}}
{{--            border-radius: 15px;--}}
{{--            text-align: center;--}}
{{--            transition: transform 0.3s, box-shadow 0.3s;--}}
{{--            overflow: hidden;--}}
{{--            position: relative;--}}
{{--        }--}}

{{--        .building-item:hover, .classroom-item:hover, .asset-item:hover {--}}
{{--            transform: translateY(-10px);--}}
{{--            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);--}}
{{--            cursor: pointer;--}}
{{--        }--}}

{{--        .building-content, .classroom-content, .asset-content {--}}
{{--            position: relative;--}}
{{--            z-index: 1;--}}
{{--        }--}}

{{--        .building-item::before, .classroom-item::before, .asset-item::before {--}}
{{--            content: '';--}}
{{--            position: absolute;--}}
{{--            top: 0;--}}
{{--            left: 0;--}}
{{--            width: 100%;--}}
{{--            height: 100%;--}}
{{--            z-index: 0;--}}
{{--            transition: opacity 0.3s;--}}
{{--            opacity: 0;--}}
{{--        }--}}

{{--        .building-item:hover::before, .classroom-item:hover::before, .asset-item:hover::before {--}}
{{--            opacity: 1;--}}
{{--        }--}}

{{--        .building-item h3, .classroom-item h3, .asset-item h3 {--}}
{{--            margin: 0;--}}
{{--            font-size: 1rem;--}}
{{--            text-transform: uppercase;--}}
{{--        }--}}

{{--        .active {--}}
{{--            background-color: #a855f7; /* Example active color */--}}
{{--            color: white;--}}
{{--        }--}}
{{--    </style>--}}
{{--    <ul class="building-grid">--}}
{{--        @foreach ($buildings as $building)--}}
{{--            <li class="building-item {{ $selectedBuildingId == $building->id ? 'active' : '' }} text-primary-50 hover:bg-primary-600" wire:click="loadClassrooms({{ $building->id }})">--}}
{{--                <div class="building-content">--}}
{{--                    <h3>{{ $building->name }}</h3>--}}
{{--                </div>--}}
{{--            </li>--}}
{{--        @endforeach--}}
{{--    </ul>--}}

{{--    @if (!empty($classrooms))--}}
{{--        <div class="space">--}}
{{--            <x-filament::section>--}}
{{--                <ul class="classroom-grid">--}}
{{--                    @foreach ($classrooms as $classroom)--}}
{{--                        <li class="classroom-item {{ $selectedClassroomId == $classroom->id ? 'active' : '' }} bg-secondary-500 text-secondary-50 hover:bg-secondary-600" wire:click="loadAssets({{ $classroom->id }})">--}}
{{--                            <div class="classroom-content">--}}
{{--                                <h3>{{ $classroom->name }}</h3>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            </x-filament::section>--}}
{{--        </div>--}}
{{--        @if (!empty($assets))--}}
{{--            <div class="space">--}}
{{--                <x-filament::section>--}}
{{--                    <ul class="asset-grid">--}}
{{--                        @foreach ($assets as $asset)--}}
{{--                            <li class="asset-item text-primary-50 hover:bg-primary-600">--}}
{{--                                <div class="asset-content">--}}
{{--                                    <h3>{{ $asset->name }}</h3>--}}
{{--                                    <p>{{ $asset->asset_list }}</p>--}}
{{--                                    <x-filament::badge>--}}
{{--                                        {{ $asset->status }}--}}
{{--                                    </x-filament::badge>--}}
{{--                                </div>--}}
{{--                            </li>--}}
{{--                        @endforeach--}}
{{--                    </ul>--}}
{{--                </x-filament::section>--}}
{{--            </div>--}}
{{--        @endif--}}
{{--    @endif--}}
</div>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $cv['user_name'] ?? 'CV' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #ffffff; margin: 0; }
        .wrapper { display: flex; width: 100%; min-height: 100vh; }
        .left-col { width: 38%; background-color: #1a2e3a; color: #f8fafc; padding: 40px 30px; }
        .right-col { width: 62%; background-color: #ffffff; color: #334155; padding: 40px; }
        .left-box { border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 8px; font-weight: 600; letter-spacing: 1px; margin-bottom: 20px; font-size: 15px; text-transform: uppercase; color: #60a5fa; }
        .right-box { border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; font-weight: 700; letter-spacing: 1px; margin-bottom: 22px; font-size: 16px; text-transform: uppercase; color: #1e293b; }
        a { text-decoration: none; color: inherit; }
    </style>
</head>
<body dir="{{ $dir }}">
@php
function getBase64Image($url) {
    if (empty($url)) return '';

    // Intercept ANY URL that contains '/storage/' which signifies a local App asset.
    if (str_contains($url, '/storage/')) {
        $parts = explode('/storage/', $url);
        $relativePath = explode('?', end($parts))[0];
        $path = storage_path('app/public/' . ltrim($relativePath, '/'));

        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . ($type === 'jpg' ? 'jpeg' : $type) . ';base64,' . base64_encode($data);
        }
    }

    // External URLs (with timeout to prevent freezing the PDF generation for 60s)
    if (str_contains($url, 'localhost') || str_contains($url, 'nginx')) return '';
    try {
        $ctx = stream_context_create([
            'http' => ['timeout' => 2]
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data) {
            $type = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpeg';
            return 'data:image/' . ($type === 'jpg' ? 'jpeg' : $type) . ';base64,' . base64_encode($data);
        }
    } catch (\Exception $e) {}

    return $url;
}

function fmt($d) {
    if (!$d) return 'Present';
    return (new \DateTime($d))->format('M Y');
}
function fmty($d) {
    if (!$d) return 'Present';
    return (new \DateTime($d))->format('Y');
}
function getDots($level) {
    $l = strtolower((string)$level);
    if (str_contains($l, 'native') || str_contains($l, 'fluent')) return 5;
    if (str_contains($l, 'advanced') || str_contains($l, 'proficient')) return 4;
    if (str_contains($l, 'intermediate') || str_contains($l, 'conversational')) return 3;
    if (str_contains($l, 'beginner') || str_contains($l, 'basic') || str_contains($l, 'elementary')) return 2;
    return 3;
}
@endphp
<div class="wrapper">
    <div class="left-col">
        @if (!empty($cv['photo']))
        <div style="text-align: center; margin-bottom: 25px;">
            <img src="{{ getBase64Image($cv['photo']) }}" alt="" style="width:140px; height:140px; border-radius:50%; object-fit:cover; border: 4px solid rgba(255,255,255,0.15);" />
        </div>
        @endif

        @if (!empty($cv['phone']) || !empty($cv['contact_email']) || !empty($cv['location']) || !empty($cv['website']) || !empty($cv['linkedin']) || !empty($cv['github']))
        <div style="margin-bottom: 30px;">
            <div class="left-box">CONTACT</div>
            <div style="font-size: 12.5px; line-height: 1.8; display:flex; flex-direction:column; gap:10px;">
                @if (!empty($cv['phone']))
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span> {{ $cv['phone'] }}
                </div>
                @endif
                @if (!empty($cv['contact_email']))
                <div style="display:flex; align-items:center; gap:10px; word-break: break-all;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></span> {{ $cv['contact_email'] }}
                </div>
                @endif
                @if (!empty($cv['location']))
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span> {{ $cv['location'] }}
                </div>
                @endif
                @if (!empty($cv['website']))
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg></span> {{ preg_replace('#^https?://#', '', $cv['website']) }}
                </div>
                @endif
                @if (!empty($cv['linkedin']))
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></span> {{ preg_replace('#^https?://(www\.)?#', '', $cv['linkedin']) }}
                </div>
                @endif
                @if (!empty($cv['github']))
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="opacity:0.75; display:flex; align-items:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg></span> {{ preg_replace('#^https?://(www\.)?#', '', $cv['github']) }}
                </div>
                @endif
            </div>
        </div>
        @endif

        @if (!empty($cv['educations']) && count($cv['educations']))
        <div style="margin-bottom: 30px;">
            <div class="left-box">EDUCATION</div>
            <div style="display:flex; flex-direction:column; gap:14px;">
                @foreach($cv['educations'] as $edu)
                <div style="background: rgba(0, 0, 0, 0.15); padding: 12px 14px; border-radius: 6px; border-left: 3px solid #60a5fa; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <div style="font-weight:700; margin-bottom:4px; font-size:13.5px; letter-spacing: 0.3px; color: #f8fafc;">{{ $edu['degree'] }}</div>
                    <div style="font-weight:400; opacity:0.9; margin-bottom: 6px; font-size: 12.5px;">{{ $edu['institution'] }}</div>
                    <div style="font-size: 10.5px; font-weight: 600; text-transform: uppercase; padding: 3px 6px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; display: inline-block;">
                        {{ fmty($edu['start_date'] ?? null) }} - {{ fmty($edu['end_date'] ?? null) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($cv['skills']) && count($cv['skills']))
        <div style="margin-bottom: 30px;">
            <div class="left-box">SKILLS</div>
            <div style="display:flex; flex-wrap: wrap; gap: 10px;">
                @foreach($cv['skills'] as $skill)
                <div style="background: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.08); padding: 8px 14px; border-radius: 4px; font-size: 13px; font-weight: 500; color: #f8fafc; letter-spacing: 0.3px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    {{ $skill['name'] }}
                    @if(!empty($skill['level']))
                        <span style="opacity: 0.7; font-size: 10px; text-transform: uppercase; margin-left: 6px; padding-left: 8px; border-left: 1px solid rgba(255,255,255,0.25);">{{ $skill['level'] }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($cv['languages']) && count($cv['languages']))
        <div>
            <div class="left-box">LANGUAGE</div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach($cv['languages'] as $lang)
                <div style="display:flex; justify-content:space-between; align-items:center; background: rgba(0, 0, 0, 0.15); padding: 10px 14px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <span style="font-size:13.5px; font-weight: 600; color: #f8fafc; letter-spacing: 0.3px;">{{ $lang['name'] }}</span>
                    <span style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; padding: 4px 8px; background: rgba(56, 189, 248, 0.15); color: #7dd3fc; border-radius: 4px;">
                        {{ $lang['level'] ?? 'Beginner' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="right-col">
        <div style="margin-bottom: 30px; position:relative; padding-bottom: 24px;">
            <div style="position:absolute; bottom:0; left:0; width:80px; height:5px; background: linear-gradient(90deg, #3b82f6, #a855f7, #10b981); border-radius: 4px;"></div>
            <h1 style="font-size: 46px; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; line-height: 1.05; letter-spacing: -1.5px; text-transform: uppercase;">
                {{ $cv['user_name'] ?? '' }}
            </h1>
            <h2 style="display:inline-block; font-size: 14px; font-weight: 700; color: #3b82f6; background: #eff6ff; padding: 8px 16px; border-radius: 20px; margin: 0; letter-spacing: 1.5px; text-transform: uppercase; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.2);">
                {{ $cv['headline'] ?? '' }}
            </h2>
        </div>

        @if (!empty($cv['summary']))
        <div style="margin-bottom: 25px;">
            <div class="right-box">ABOUT ME</div>
            <div style="background: #f0fdf4; padding: 18px; border-radius: 8px; border-left: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <p style="font-size: 13.5px; line-height: 1.7; margin: 0; color: #334155; white-space: pre-wrap;">{{ ltrim(preg_replace('/\n\s+/', "\n", $cv['summary'])) }}</p>
            </div>
        </div>
        @endif

        @if (!empty($cv['experiences']) && count($cv['experiences']))
        <div style="margin-bottom: 25px;">
            <div class="right-box">EXPERIENCE</div>
            <div style="display:flex; flex-direction:column; gap:18px;">
                @foreach($cv['experiences'] as $exp)
                <div style="display:flex; gap:15px; position:relative; background: #eff6ff; padding: 16px; border-radius: 8px; border-left: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="width: 110px; flex-shrink:0; text-align:right;">
                        <div style="font-size:12px; font-weight:700; color:#0f172a; margin-bottom:2px;">
                            {{ fmt($exp['start_date'] ?? null) }} - <br/>{{ fmt($exp['end_date'] ?? null) }}
                        </div>
                        <div style="font-size:12px; font-weight:600; color:#2563eb;">
                            {{ $exp['company'] }}
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:6px; text-transform:uppercase;">
                            {{ $exp['position'] }}
                        </div>
                        <p style="font-size:13px; line-height:1.6; margin:0; color:#334155; white-space: pre-wrap;">{{ ltrim(preg_replace('/\n\s+/', "\n", $exp['description'] ?? '')) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($cv['projects']) && count($cv['projects']))
        <div style="margin-bottom: 25px;">
            <div class="right-box">PROJECTS</div>
            <div style="display:flex; flex-direction:column; gap:18px;">
                @foreach($cv['projects'] as $proj)
                <div style="display:flex; gap:15px; position:relative; background: #faf5ff; padding: 14px; border-radius: 8px; border-left: 4px solid #a855f7;">
                    <div style="width: 110px; flex-shrink:0; text-align:right;">
                        <div style="font-size:12px; font-weight:700; color:#0f172a; margin-bottom:2px;">
                            @if (!empty($proj['start_date']) || !empty($proj['date']))
                                {{ fmt($proj['start_date'] ?? $proj['date'] ?? null) }}
                            @endif
                            @if (!empty($proj['end_date']))
                                - <br/>{{ fmt($proj['end_date'] ?? null) }}
                            @endif
                        </div>
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex; gap:14px; align-items:flex-start;">
                            @if (!empty($proj['cover']))
                            <div style="width: 60px; height: 60px; flex-shrink: 0; border-radius: 6px; overflow: hidden; border: 1px solid #eaeaea; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <img src="{{ getBase64Image($proj['cover']) }}" style="width:100%; height:100%; object-fit:cover;" />
                            </div>
                            @endif
                            <div style="flex:1;">
                                <div style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:4px; text-transform:uppercase;">
                                    {{ $proj['title'] }}
                                </div>
                                <p style="font-size:13px; line-height:1.6; margin:0; color:#334155; white-space: pre-wrap;">{{ ltrim(preg_replace('/\n\s+/', "\n", $proj['description'] ?? '')) }}</p>
                                @if (!empty($proj['link']) || !empty($proj['github_url']))
                                <div style="margin-top: 6px; font-size: 11px; font-weight: 600; display:flex; align-items:center;">
                                    <span style="display:inline-flex; align-items:center; margin-right:4px; opacity:0.8;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg></span> <a href="{{ $proj['link'] ?? $proj['github_url'] }}" style="color: #9333ea; text-decoration: none;">{{ preg_replace('#^https?://(www\.)?#', '', $proj['link'] ?? $proj['github_url']) }}</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($cv['volunteer_experiences']) && count($cv['volunteer_experiences']))
        <div style="margin-bottom: 25px;">
            <div class="right-box">VOLUNTEER EXPERIENCE</div>
            <div style="display:flex; flex-direction:column; gap:16px;">
                @foreach($cv['volunteer_experiences'] as $vol)
                <div style="display:flex; align-items: flex-start; gap:12px; position:relative; background: #f8fafc; padding: 14px; border-radius: 8px; border-left: 4px solid #60a5fa;">
                    <div style="flex:1;">
                        <div style="display:flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                            <div>
                                <div style="font-size:15px; font-weight:700; color:#0f172a; text-transform:uppercase; margin-bottom:2px;">
                                    {{ $vol['role'] }}
                                </div>
                                <div style="font-size:13px; font-weight:600; color:#3b82f6;">
                                    {{ $vol['organization'] }}
                                </div>
                            </div>
                            <div style="font-size:12px; font-weight:600; color:#64748b; background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; white-space: nowrap;">
                                {{ fmt($vol['start_date'] ?? null) }} – {{ fmt($vol['end_date'] ?? null) }}
                            </div>
                        </div>
                        @if (!empty($vol['description']))
                        <p style="font-size:12.5px; line-height:1.5; margin:8px 0 0 0; color:#475569; white-space: pre-wrap;">{{ ltrim(preg_replace('/\n\s+/', "\n", $vol['description'])) }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($cv['certifications']) && count($cv['certifications']))
        <div style="margin-bottom: 25px;">
            <div class="right-box">CERTIFICATIONS</div>
            <div style="display:flex; flex-direction:column; gap:18px;">
                @foreach($cv['certifications'] as $cert)
                <div style="display:flex; gap:15px; position:relative; background: #fffdf4; padding: 16px; border-radius: 8px; border-left: 4px solid #f5a623; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="width: 110px; flex-shrink:0; text-align:right;">
                        <div style="font-size:12px; font-weight:800; color:#0f172a; margin-bottom:4px;">
                            {{ !empty($cert['date']) || !empty($cert['issue_date']) ? fmt($cert['date'] ?? $cert['issue_date'] ?? null) : '' }}
                        </div>
                        @if (!empty($cert['expiration_date']))
                        <div style="font-size:10.5px; font-weight:700; color:#ef4444; background:#fef2f2; padding:2px 6px; border-radius:4px; display:inline-block;">
                            EXP: {{ fmt($cert['expiration_date']) }}
                        </div>
                        @endif
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex; gap:16px; align-items:center;">
                            @if (!empty($cert['photo']))
                            <div style="width: 52px; height: 52px; flex-shrink: 0; border-radius: 8px; overflow: hidden; border: 2px solid #fef08a; background: #fff; box-shadow: 0 2px 5px rgba(245, 166, 35, 0.15);">
                                <img src="{{ getBase64Image($cert['photo']) }}" style="width:100%; height:100%; object-fit:cover; padding: 2px;" />
                            </div>
                            @endif
                            <div style="flex:1;">
                                <div style="font-size:15.5px; font-weight:800; color:#0f172a; margin-bottom:5px; text-transform:uppercase; letter-spacing: -0.2px;">
                                    {{ $cert['name'] }}
                                </div>
                                <div style="display:inline-block; font-size:12px; font-weight:700; color:#d97706; background:#fef3c7; padding:3px 10px; border-radius:12px; border: 1px solid rgba(217, 119, 6, 0.15);">
                                    {{ $cert['issuer'] ?? $cert['organization'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
</body>
</html>
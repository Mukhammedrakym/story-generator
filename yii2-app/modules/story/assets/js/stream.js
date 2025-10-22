async function streamStory(url, payload, onChunk, onDone, onError) {
    try {
        const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
        if (!res.ok || !res.body) throw new Error(`HTTP ${res.status}: ${await res.text()}`);
        const reader = res.body.getReader(), decoder = new TextDecoder(); let full = '';
        while (true) { const {value, done} = await reader.read(); if (done) break;
            const chunk = decoder.decode(value, {stream:true}); full += chunk; onChunk && onChunk(chunk); }
        onDone && onDone(full);
    } catch (e) { onError && onError(e); }
}
window.__StoryStream = { streamStory };

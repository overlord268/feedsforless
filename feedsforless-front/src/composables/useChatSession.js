const STORAGE_KEY = 'ffl_chat_session';

export function getChatSession() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (parsed?.conversation_id && parsed?.access_token) return parsed;
  } catch {
    /* ignore */
  }
  return null;
}

export function saveChatSession(session) {
  if (!session?.conversation_id || !session?.access_token) return;
  localStorage.setItem(STORAGE_KEY, JSON.stringify({
    conversation_id: session.conversation_id,
    access_token: session.access_token,
  }));
}

export function clearChatSession() {
  localStorage.removeItem(STORAGE_KEY);
}

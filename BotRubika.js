import fetch from "node-fetch";

class BotRubika {
    constructor(token, timeout = 15000, userAgent = null) {
        this.token = token;
        this.baseUrl = "https://botapi.rubika.ir/v3/";
        this.timeout = timeout;
        this.userAgent = userAgent;
    }

    async call(method, payload = {}) {
        const url = `${this.baseUrl}${encodeURIComponent(this.token)}/${method}`;
        const headers = { "Content-Type": "application/json" };
        if (this.userAgent) headers["User-Agent"] = this.userAgent;

        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), this.timeout);

        let resp;
        try {
            resp = await fetch(url, {
                method: "POST",
                headers,
                body: JSON.stringify(payload),
                signal: controller.signal,
            });
        } catch (err) {
            clearTimeout(id);
            throw new Error("Fetch error: " + err.message);
        }
        clearTimeout(id);

        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}: ${await resp.text()}`);
        }

        let data;
        try {
            data = await resp.json();
        } catch (err) {
            throw new Error("JSON parse error: " + err.message);
        }
        return data;
    }

    // ========== API Methods ==========
    getMe() {
        return this.call("getMe");
    }

    sendMessage(chatId, text, chatKeypad = null, chatKeypadType = null, inlineKeypad = null, disableNotification = false, replyToMessageId = null) {
        const payload = { chat_id: chatId, text };
        if (chatKeypadType !== null) payload.chat_keypad_type = chatKeypadType;
        if (chatKeypad) payload.chat_keypad = chatKeypad.toArray();
        if (inlineKeypad) payload.inline_keypad = inlineKeypad.toArray();
        if (disableNotification) payload.disable_notification = true;
        if (replyToMessageId) payload.reply_to_message_id = replyToMessageId;
        return this.call("sendMessage", payload);
    }

    sendPoll(chatId, question, options) {
        return this.call("sendPoll", { chat_id: chatId, question, options: [...options] });
    }

    sendLocation(chatId, latitude, longitude) {
        return this.call("sendLocation", { chat_id: chatId, latitude, longitude });
    }

    sendContact(chatId, firstName, lastName, phoneNumber) {
        return this.call("sendContact", { chat_id: chatId, first_name: firstName, last_name: lastName, phone_number: phoneNumber });
    }

    getChat(chatId) {
        return this.call("getChat", { chat_id: chatId });
    }

    getUpdates(offsetId = null, limit = null) {
        const payload = {};
        if (offsetId !== null) payload.offset_id = offsetId;
        if (limit !== null) payload.limit = limit;
        return this.call("getUpdates", payload);
    }

    forwardMessage(fromChatId, messageId, toChatId, disableNotification = false) {
        const payload = { from_chat_id: fromChatId, message_id: messageId, to_chat_id: toChatId };
        if (disableNotification) payload.disable_notification = true;
        return this.call("forwardMessage", payload);
    }

    editMessageText(chatId, messageId, text) {
        return this.call("editMessageText", { chat_id: chatId, message_id: messageId, text });
    }

    editInlineKeypad(chatId, messageId, inlineKeypad) {
        return this.call("editInlineKeypad", { chat_id: chatId, message_id: messageId, inline_keypad: inlineKeypad.toArray() });
    }

    deleteMessage(chatId, messageId) {
        return this.call("deleteMessage", { chat_id: chatId, message_id: messageId });
    }

    setCommands(botCommands) {
        return this.call("setCommands", { bot_commands: [...botCommands] });
    }

    updateBotEndpoints(url, type) {
        return this.call("updateBotEndpoints", { url, type });
    }

    editChatKeypad(chatId, chatKeypadType, chatKeypad = null) {
        const payload = { chat_id: chatId, chat_keypad_type: chatKeypadType };
        if (chatKeypad) payload.chat_keypad = chatKeypad.toArray();
        return this.call("editChatKeypad", payload);
    }
}

// ========== Models ==========
class BotRubika_Keypad {
    constructor() {
        this.rows = [];
        this.resizeKeyboard = null;
        this.onTimeKeyboard = null;
    }
    static new() { return new this(); }
    addRow(row) { this.rows.push(row); return this; }
    setResizeKeyboard(v) { this.resizeKeyboard = v; return this; }
    setOnTimeKeyboard(v) { this.onTimeKeyboard = v; return this; }
    toArray() {
        const rows = this.rows.map(r => r.toArray());
        const out = { rows };
        if (this.resizeKeyboard !== null) out.resize_keyboard = this.resizeKeyboard;
        if (this.onTimeKeyboard !== null) out.on_time_keyboard = this.onTimeKeyboard;
        return out;
    }
}

class BotRubika_KeypadRow {
    constructor() { this.buttons = []; }
    static new() { return new this(); }
    addButton(button) { this.buttons.push(button); return this; }
    toArray() { return { buttons: this.buttons.map(b => b.toArray()) }; }
}

class BotRubika_Button {
    constructor(id, type) {
        this.id = id;
        this.type = type;
        this.buttonText = null;
    }
    static simple(id, text) {
        const b = new this(id, "Simple");
        b.buttonText = text;
        return b;
    }
    toArray() {
        const arr = { id: this.id, type: this.type };
        if (this.buttonText !== null) arr.button_text = this.buttonText;
        return arr;
    }
}

class BotRubika_InlineKeypad {
    constructor() { this.rows = []; }
    static new() { return new this(); }
    addRow(row) { this.rows.push(row); return this; }
    toArray() { return { rows: this.rows.map(r => r.toArray()) }; }
}

// ========== Webhook ==========
class BotRubika_Webhook {
    static async readJsonBody(req) {
        const chunks = [];
        for await (const chunk of req) {
            chunks.push(chunk);
        }
        const body = Buffer.concat(chunks).toString();
        let decoded;
        try {
            decoded = JSON.parse(body);
        } catch (err) {
            throw new Error("Invalid JSON: " + err.message);
        }
        return decoded;
    }

    static isReceiveUpdate(payload) {
        return !!payload.update;
    }
    static isReceiveInlineMessage(payload) {
        return !!payload.inline_message;
    }
    static getChatIdFromUpdate(payload) {
        if (payload.update?.chat_id) return String(payload.update.chat_id);
        if (payload.inline_message?.chat_id) return String(payload.inline_message.chat_id);
        return null;
    }
    static getMessageText(payload) {
        if (payload.update?.new_message?.text) return String(payload.update.new_message.text);
        if (payload.inline_message?.text) return String(payload.inline_message.text);
        return null;
    }
    static getButtonId(payload) {
        const aux = payload.update?.new_message?.aux_data || payload.inline_message?.aux_data || null;
        if (aux?.button_id) return String(aux.button_id);
        return null;
    }
}

export {
    BotRubika,
    BotRubika_Keypad,
    BotRubika_KeypadRow,
    BotRubika_Button,
    BotRubika_InlineKeypad,
    BotRubika_Webhook,
};

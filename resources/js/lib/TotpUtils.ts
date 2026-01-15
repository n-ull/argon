export class TotpUtils {
    /**
     * Generates a 6-digit TOTP code based on a Base32 token string.
     * 
     * @param token The secret token (Base32 encoded)
     * @param interval The time step in seconds (default 30)
     * @returns A promise that resolves to an object with the OTP string
     */
    static async generate(token: string, interval: number = 30): Promise<{ otp: string }> {
        const epoch = Math.floor(Date.now() / 1000);
        const counter = Math.floor(epoch / interval);

        const otp = await this.generateHOTP(token, counter);
        return { otp };
    }

    /**
     * Decodes a Base32 string into a Uint8Array.
     */
    private static base32ToBuf(base32: string): Uint8Array {
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        const cleaned = base32.replace(/=+$/, '').toUpperCase();
        const buf = new Uint8Array(Math.floor((cleaned.length * 5) / 8));
        let bits = 0;
        let value = 0;
        let index = 0;

        for (let i = 0; i < cleaned.length; i++) {
            const charIndex = alphabet.indexOf(cleaned[i]);
            if (charIndex === -1) continue;

            value = (value << 5) | charIndex;
            bits += 5;
            if (bits >= 8) {
                buf[index++] = (value >> (bits - 8)) & 255;
                bits -= 8;
            }
        }
        return buf;
    }

    /**
     * Generates a HOTP value for a specific counter.
     */
    private static async generateHOTP(secret: string, counter: number): Promise<string> {
        const keyData = this.base32ToBuf(secret);

        // Counter must be an 8-byte big-endian integer
        const counterBuffer = new ArrayBuffer(8);
        const dataView = new DataView(counterBuffer);

        // Set the counter as a 64-bit big-endian integer
        // JavaScript handles up to 2^53, so we use the bottom 32 bits for the lower part
        // and the upper part for the overflow.
        dataView.setUint32(4, counter & 0xffffffff, false);
        dataView.setUint32(0, Math.floor(counter / 0x100000000), false);

        const cryptoKey = await window.crypto.subtle.importKey(
            'raw',
            keyData.buffer as ArrayBuffer,
            { name: 'HMAC', hash: { name: 'SHA-1' } },
            false,
            ['sign']
        );

        const signature = await window.crypto.subtle.sign(
            'HMAC',
            cryptoKey,
            counterBuffer
        );

        const hmac = new Uint8Array(signature);
        const offset = hmac[hmac.length - 1] & 0x0f;

        const binary = (
            ((hmac[offset] & 0x7f) << 24) |
            ((hmac[offset + 1] & 0xff) << 16) |
            ((hmac[offset + 2] & 0xff) << 8) |
            (hmac[offset + 3] & 0xff)
        );

        const otp = binary % 1000000;
        return otp.toString().padStart(6, '0');
    }

    /**
     * Returns the remaining seconds for the current window.
     * 
     * @param interval The time step in seconds (default 30)
     * @returns Seconds remaining
     */
    static getRemainingSeconds(interval: number = 30): number {
        const now = Math.floor(Date.now() / 1000);
        return interval - (now % interval);
    }
}

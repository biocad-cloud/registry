namespace data.ZipData {

    type MetabolicEmbedding = viewer.metabolic_embedding;

    // 缓存配置
    // 修改这个版本号可以强制让用户刷新缓存（例如数据更新时）
    const CACHE_VERSION = 'v1_20231027'; 
    const CACHE_KEY = `metabolic_embedding_zip_${CACHE_VERSION}`;

    /**
     * 从URL下载zip文件并解析为MetabolicEmbedding数组
     * 优先从 localStorage 读取缓存的 ZIP Base64
     */
    export async function loadAndParseZipFromUrl(url: string): Promise<MetabolicEmbedding[]> {
        // 1. 尝试从 localStorage 读取缓存
        try {
            const cachedBase64 = localStorage.getItem(CACHE_KEY);
            if (cachedBase64) {
                console.log('[Cache] Loading data from localStorage...');
                const arrayBuffer = base64ToArrayBuffer(cachedBase64);
                // 验证数据是否有效
                if (arrayBuffer && arrayBuffer.byteLength > 0) {
                    return await parseZipToArray(arrayBuffer);
                }
            }
        } catch (error) {
            console.warn('[Cache] Failed to read cache, fetching from network...', error);
            // 如果读取失败（如解析错误），清除脏数据
            localStorage.removeItem(CACHE_KEY);
        }

        // 2. 没有缓存或缓存失效，从网络获取
        console.log('[Network] Fetching zip file from server...');
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Failed to fetch zip file: ${response.status} ${response.statusText}`);
        }
        const arrayBuffer = await response.arrayBuffer();

        // 3. 存入 localStorage (异步执行，不阻塞解析)
        // 注意：如果数据超过 localStorage 限制（通常5MB），try-catch 会捕获错误
        try {
            const base64 = arrayBufferToBase64(arrayBuffer);
            localStorage.setItem(CACHE_KEY, base64);
            console.log('[Cache] Data cached successfully.');
        } catch (error) {
            console.warn('[Cache] Failed to save to localStorage (likely quota exceeded).', error);
        }

        // 4. 解析并返回
        return parseZipToArray(arrayBuffer);
    }

    /**
     * ArrayBuffer 转 Base64 字符串
     */
    function arrayBufferToBase64(buffer: ArrayBuffer): string {
        let binary = '';
        const bytes = new Uint8Array(buffer);
        const len = bytes.byteLength;
        for (let i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    /**
     * Base64 字符串 转 ArrayBuffer
     */
    function base64ToArrayBuffer(base64: string): ArrayBuffer {
        const binaryString = window.atob(base64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes.buffer;
    }

    /**
     * 从ArrayBuffer解压并解析CSV
     */
    export async function parseZipToArray(zipBuffer: ArrayBuffer): Promise<MetabolicEmbedding[]> {
        const zip = await JSZip.loadAsync(zipBuffer);

        // 找到zip中的csv文件
        const csvFile = Object.keys(zip.files).find(
            filename => filename.toLowerCase().endsWith('.csv')
        );

        if (!csvFile) {
            throw new Error('No CSV file found in the zip archive');
        }

        const csvText = await zip.file(csvFile)!.async('string');
        return parseCsvText(csvText);
    }

    /**
     * 解析CSV文本为MetabolicEmbedding数组
     */
    export function parseCsvText(csvText: string): MetabolicEmbedding[] {
        const lines = csvText.trim().split('\n');

        if (lines.length < 2) {
            throw new Error('CSV file is empty or has no data rows');
        }

        // 解析表头
        const headers = parseCsvLine(lines[0]);

        // 定义列索引映射
        const columnMap = {
            assembly_id: headers.indexOf('assembly_id'),
            dimension_1: headers.indexOf('dimension_1'),
            dimension_2: headers.indexOf('dimension_2'),
            dimension_3: headers.indexOf('dimension_3'),
            ec_1: headers.indexOf('1.-.-.-'),
            ec_2: headers.indexOf('2.-.-.-'),
            ec_3: headers.indexOf('3.-.-.-'),
            ec_4: headers.indexOf('4.-.-.-'),
            ec_5: headers.indexOf('5.-.-.-'),
            ec_6: headers.indexOf('6.-.-.-'),
            ec_7: headers.indexOf('7.-.-.-'),
            scientific_name: headers.indexOf('scientific_name'),
            kingdom: headers.indexOf('kingdom'),
            phylum: headers.indexOf('phylum'),
            class: headers.indexOf('class'),
            order: headers.indexOf('order'),
            family: headers.indexOf('family'),
            genus: headers.indexOf('genus'),
            species: headers.indexOf('species'),
            ncbi_taxid: headers.indexOf('ncbi_taxid')
        };

        // 验证必要列存在
        const requiredColumns = ['assembly_id', 'dimension_1', 'dimension_2', 'dimension_3'];
        for (const col of requiredColumns) {
            if ((columnMap as any)[col] === -1) {
                throw new Error(`Required column "${col}" not found in CSV header`);
            }
        }

        const result: MetabolicEmbedding[] = [];

        // 解析数据行
        for (let i = 1; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue; // 跳过空行

            const values = parseCsvLine(line);

            const getNumber = (colName: string): number => {
                const idx = (columnMap as any)[colName];
                const val = idx >= 0 ? values[idx] : '0';
                return parseFloat(val) || 0;
            };

            const getString = (colName: string): string => {
                const idx = (columnMap as any)[colName];
                return idx >= 0 ? (values[idx] || '') : '';
            };

            result.push({
                assembly_id: getString('assembly_id'),
                x: getNumber('dimension_1'),
                y: getNumber('dimension_2'),
                z: getNumber('dimension_3'),
                ec_number: [
                    getNumber('ec_1'),
                    getNumber('ec_2'),
                    getNumber('ec_3'),
                    getNumber('ec_4'),
                    getNumber('ec_5'),
                    getNumber('ec_6'),
                    getNumber('ec_7'),
                ],
                scientific_name: getString('scientific_name'),
                ncbi_taxid: getString('ncbi_taxid'),
                kingdom: getString('kingdom'),
                phylum: getString('phylum'),
                class: getString('class'),
                order: getString('order'),
                family: getString('family'),
                genus: getString('genus'),
                species: getString('species')
            });
        }

        return result;
    }

    /**
     * 解析单行CSV，正确处理引号包裹的字段
     */
    function parseCsvLine(line: string): string[] {
        const result: string[] = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < line.length; i++) {
            const char = line[i];

            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    // 转义的引号
                    current += '"';
                    i++;
                } else {
                    // 切换引号状态
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current);
                current = '';
            } else {
                current += char;
            }
        }

        result.push(current);
        return result;
    }
}

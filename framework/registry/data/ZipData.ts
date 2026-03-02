namespace data.ZipData {

    type MetabolicEmbedding = viewer.metabolic_embedding;

    /**
     * 从URL下载zip文件并解析为MetabolicEmbedding数组
     */
    export async function loadAndParseZipFromUrl(url: string): Promise<MetabolicEmbedding[]> {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Failed to fetch zip file: ${response.status} ${response.statusText}`);
        }
        const arrayBuffer = await response.arrayBuffer();
        return parseZipToArray(arrayBuffer);
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
                ncbi_taxid: '', // CSV中没有此列，设为空字符串
                kingdom: getString('kingdom'),
                phylum: getString('phylum'),
                class: getString('class'),
                order: getString('order'),
                family: getString('family'),
                genus: getString('genus'),
                species: getString('species'),
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
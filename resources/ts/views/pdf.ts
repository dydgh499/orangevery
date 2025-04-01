import html2canvas from "html2canvas"
import jsPDF from 'jspdf'


export const htmlToPDF = () => {
    const getPDFSettings = async (_content: HTMLElement, _scale: number, _windowWidth: number, _windowHeight: number, _height: number) => {
        const fullCanvas = await html2canvas(_content, {
            scale: _scale, // 기본값은 1, 더 높은 값으로 설정하면 고해상도로 캡처
            useCORS: true, // 외부 리소스를 로드할 때 CORS 문제가 발생하지 않도록 설정
            letterRendering: true, // 텍스트 렌더링 정확도를 높임
            allowTaint: true, // Cross-Origin 이미지를 허용할 경우
            windowWidth: _windowWidth, // 실제 너비 _content.offsetWidth
            windowHeight: _windowHeight,
            height: _height,
        })
        return fullCanvas
    }

    const createPDF = async (_content: HTMLElement, _pdf_width: number, _pdf_height_rate: number, _scale: number, _windowWidth: number, _windowHeight: number, _height: number, _pdf_name: string) => {
        const fullCanvas = await getPDFSettings(_content, _scale, _windowWidth, _windowHeight, _height)
        const imgData = fullCanvas.toDataURL('image/png')
        const pdf = new jsPDF({
            orientation: 'portrait',
            format: 'a4',
            unit: 'px',
        });
        const pageHeight = pdf.internal.pageSize.getHeight()
        let currentHeight = 0

        while (currentHeight < fullCanvas.height / _pdf_height_rate) {
            pdf.addPage()
            pdf.addImage(imgData, 'PNG', 0, -currentHeight, _pdf_width, fullCanvas.height / _pdf_height_rate, '', 'FAST', 0)
            currentHeight += pageHeight
            if (currentHeight > fullCanvas.height) {
                pdf.deletePage(pdf.getNumberOfPages())
            }
        }

        pdf.deletePage(1)
        pdf.save(`거래내역조회서_${_pdf_name}.pdf`)
    }

    return {
        createPDF,
    }
}
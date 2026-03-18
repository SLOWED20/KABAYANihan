// Tourist Foot Traffic Chart with Trend Line + Tooltip Comparison + Seasonal Highlight + Export
const ctx = document.getElementById("footTrafficChart").getContext("2d");

const visitors = [1200, 1500, 1800, 2200, 2500, 2700, 3000, 2800, 2600, 2400, 2000, 1700];
const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

const avgGrowth = (visitors[visitors.length - 1] - visitors[0]) / (visitors.length - 1);
const trendLine = visitors.map((_, i) => visitors[0] + avgGrowth * i);

new Chart(ctx, {
    data: {
        labels: months,
        datasets: [
            {
                type: "bar", label: "Visitors to Mount Pulag", data: visitors,
                backgroundColor: "rgba(13,110,253,0.6)", borderColor: "#0d6efd", borderWidth: 1
            },
            {
                type: "line", label: "Trend Line", data: trendLine,
                borderColor: "#198754", borderWidth: 2, fill: false, tension: 0.3, pointRadius: 0
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: (ctx) => (ctx.dataset.label || "") + ": " + ctx.raw.toLocaleString(),
                    afterBody: (ctx) => {
                        const i = ctx[0].dataIndex;
                        return [
                            "Actual Visitors: " + visitors[i].toLocaleString(),
                            "Trend Value: " + Math.round(trendLine[i]).toLocaleString()
                        ];
                    }
                }
            },
            annotation: {
                annotations: {
                    peakSeason: {
                        type: "box", xMin: "May", xMax: "Jul",
                        backgroundColor: "rgba(255,193,7,0.2)",
                        borderColor: "rgba(255,193,7,0.8)", borderWidth: 1,
                        label: { content: "Peak Season", enabled: true, position: "start" }
                    }
                }
            }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: "Number of Visitors" } },
            x: { title: { display: true, text: "Month" } }
        }
    }
});

// CSV Export
function downloadCSV(data, labels) {
    let csv = "Month,Visitors,Trend Value\n";
    labels.forEach((m, i) => { csv += `${m},${data.visitors[i]},${Math.round(data.trendLine[i])}\n`; });
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "mount_pulag_foot_traffic.csv";
    link.click();
}
document.getElementById("downloadCSV").addEventListener("click", () => downloadCSV({ visitors, trendLine }, months));

// PDF Export with Date Stamp
document.getElementById("downloadPDF").addEventListener("click", () => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();
    pdf.setFontSize(16);
    pdf.text("Mount Pulag Tourist Foot Traffic Report", 20, 20);
    const today = new Date();
    const dateStr = today.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
    pdf.setFontSize(10);
    pdf.text("Generated on: " + dateStr, 20, 30);
    const canvas = document.getElementById("footTrafficChart");
    const chartImage = canvas.toDataURL("image/png", 1.0);
    pdf.addImage(chartImage, "PNG", 15, 40, 180, 100);
    pdf.save("mount_pulag_foot_traffic.pdf");
});
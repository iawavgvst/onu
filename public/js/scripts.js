$('#loadBtn').on('click', function () {
    $('#loading').show();
    $('#result').empty();

    $.ajax({
        url: '/api/load-onu-data',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            $('#loading').hide();
            renderTable(data);
        },
        error: function (xhr) {
            $('#loading').hide();
            alert('Ошибка загрузки данных: ' + xhr.responseJSON.error);
        }
    });
});

function renderTable(data) {
    if (!data || data.length === 0) {
        $('#result').html('<p>Нет данных для отображения.</p>');
        return;
    }

    let html = '<table><thead><tr>';
    // Заголовки таблицы
    html += '<th>IntfName</th>';
    html += '<th>VendorID</th><th>ModelID</th><th>SN</th><th>LOID</th><th>Status</th><th>Config Status</th><th>Active Time</th>';
    html += '<th>Temp(degree)</th><th>Volt(V)</th><th>Bias(mA)</th><th>TxPow(dBm)</th><th>RxPow(dBm)</th>';
    html += '</tr></thead><tbody>';

    data.forEach(item => {
        const d = item.data;
        const s = item.stats || {};

        html += '<tr>';
        html += `<td>${d.interface}</td>`;
        html += `<td>${d.vendor_id}</td>`;
        html += `<td>${d.model_id}</td>`;
        html += `<td>${d.sn}</td>`;
        html += `<td>${d.loid}</td>`;
        html += `<td>${d.status}</td>`;
        html += `<td>${d.config_status}</td>`;
        html += `<td>${d.active_time}</td>`;

        html += `<td>${s.temperature}</td>`;
        html += `<td>${s.voltage}</td>`;
        html += `<td>${s.bias}</td>`;
        html += `<td>${s.tx_power}</td>`;
        html += `<td>${s.rx_power}</td>`;
        html += '</tr>';
    });

    html += '</tbody></table>';

    $('#result').html(html);
}

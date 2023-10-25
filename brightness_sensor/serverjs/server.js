const express = require('express');
const cors = require('cors');
const mqtt = require('mqtt');
const mysql = require('mysql');

const app = express();
app.use(cors());

const db_config = {
	host: "127.0.0.1",
	user: "root",
	password: "",
	database: "mqtt_esp32"
};

const sql_con = mysql.createConnection(db_config);

function handleMySQLDisconnect() {
	sql_con.connect(function (err) {
		if (err) {
			console.log('Lỗi khi kết nối đến cơ sở dữ liệu:', err);
			setTimeout(handleMySQLDisconnect, 2000);
		}
		console.log("Đã kết nối đến cơ sở dữ liệu!");
	});

	sql_con.on('error', function (err) {
		console.log('Lỗi cơ sở dữ liệu', err);
		if (err.code === 'PROTOCOL_CONNECTION_LOST') {
			handleMySQLDisconnect();
		} else {
			throw err;
		}
	});
}

handleMySQLDisconnect();

const options = {
	host: 'cfdc549178f54de381adaf8a1f088efc.s2.eu.hivemq.cloud',
	port: 8883,
	protocol: 'mqtts',
	username: 'mikehung611',
	password: 'Nguyenvanhung2002'
}

const client = mqtt.connect(options);


// http://localhost:3000/control?val=1
app.get('/control', function (req, res) {
	var val = req.query.val;

	if (val !== '0' && val !== '1') {
        return res.status(400).send("Giá trị VAL không hợp lệ. Nó phải là 0 hoặc 1.");
    }

	client.publish('relay', val, function (err) {
		if (err) {
			res.send("FAILED");
		}
		else {
			INSERT_RELAY_DATA(val);
			res.send("OK");
		}
	});
})

client.on('connect', function () {
	client.subscribe('data', function (err) {
		if (err) {
			console.log("Lỗi khi subscribe sensor/update topic", err);
		} else {
			console.log("Đã subscribe sensor/update topic");
		}
	});

	client.subscribe('relay', function (err) {
		if (err) {
			console.log("Lỗi khi subscribe relay/state topic", err);
		} else {
			console.log("Đã subscribe relay/state topic");
		}
	});
});

client.on('message', function (topic, message) {
	const msg_str = message.toString();
	console.log("[Topic arrived] " + topic);
	console.log("[Message arrived] " + msg_str);

	if (topic == "data") {
		const data = JSON.parse(msg_str);
		console.log(data);
		INSERT_SENSOR_DATA(data.light);
	} else if (topic == "relay") {
		const data = JSON.parse(msg_str);
		console.log(data);
		INSERT_RELAY_DATA(data.sub);
	}
});

function INSERT_SENSOR_DATA(value) {
	const sql = `INSERT INTO sensor (datetime, brightness) VALUES (NOW(), ${value})`;

	sql_con.query(sql, [value], function (err, result) {
		if (err) {
			console.log("Lỗi khi chèn dữ liệu cảm biến:", err);
		} else {
			console.log("Chèn dữ liệu cảm biến thành công!");
		}
	});
}

function INSERT_RELAY_DATA(state) {
	const sql = `INSERT INTO relay (datetime, state) VALUES (NOW(), ${state})`;

	sql_con.query(sql, [state], function (err, result) {
		if (err) {
			console.log("Lỗi khi chèn dữ liệu relay:", err);
		} else {
			console.log("Chèn dữ liệu relay thành công!");
		}
	});
}

const server = app.listen(3000, () => {
	console.log(`Server đang chạy → PORT ${server.address().port}`);
});


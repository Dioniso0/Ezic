<?php
	require_once '../DB_Connection.php';
	require_once 'Schedule.php';
	require_once 'Administration.php';


	class Print_Class
	{
		private $mpdf;
		private $schedule;
		private $administration;
		private $connection;
		private $_print;
		private $stylesheet;

		function __construct()
		{
			require_once '../../mpdf/mpdf.php';
			$this->connection = new Connection();
			$this->connection->Connect();

			$this->schedule = new Schedule();
			$this->administration = new Administration();
		}

		function getSchedule($type, $id)
		{
			if ($type == 'S') {
				$query = "
				SELECT * FROM student s 
				INNER JOIN section sn ON s.idSection = sn.idSection 
				INNER JOIN level l ON sn.idLevel = l.idLevel
				INNER JOIN specialty sy ON sn.idSpecialty = sy.idSpecialty
				WHERE idStudent = '$id'";
			}else{
				$query = "SELECT * FROM  teacher WHERE idTeacher  = '$id'";
			}

			$res = $this->connection->connection->query($query);
			$row = $res->fetch_assoc();

			$title = ($type == 'S' ? $row['level'] . ($row['level'] == 1 ? 'er' : ($row['level'] == 2 ? 'do' : 'er')) . ' Año de Bachillerato, Sección <i>"' . $row['sectionIdentifier'] . '"</i>.<br>Opción: ' . $row['sName']  : $row['idTeacher'] . "<br>" . $row['name'] . " " . $row['lastName'] . "<br>" . $row['profession']) . ".";

			$name = ($type == 'S' ? $row['level'] . ($row['level'] == 1 || $row['level'] == 3 ? 'ro' : 'do') . "_" . $row['sectionIdentifier'] : $row['idTeacher']) . "(Horario)";

			$this->stylesheet = file_get_contents('../../mpdf/resources/schedule.css');
			$this->_print = $this->schedule->printSchedule($type, $id, $title);

			$this->mpdf = new mPDF('utf-8', 'A4-L', 0, '', 10, 10, 10, 0, 0, 0, 'L');
			$this->mpdf->setTitle('Ezic: Horarios.');
			$this->mpdf->setAuthor('Ezic ©');
			$this->openPDF($title, $name);
		}

		function getRecord($id)
		{
			$this->stylesheet = file_get_contents('../../mpdf/resources/record.css');
			$auxCSS = file_get_contents('../../mpdf/resources/colors.css');

			$this->_print = $this->administration->printRecord($id);

			// $this->mpdf = new mPDF('utf-8', 'A4', 0, '', 10, 10, 10, 0, 0, 0, 'P');
			$this->mpdf = new mPDF();
			$this->mpdf->WriteHTML($auxCSS, 1);
			$this->mpdf->setTitle('Ezic: Records.');
			$this->mpdf->setAuthor('Ezic ©');
			$this->openPDF("Récord Conductual", ($id . "_record"));
			
		}

		function genHeader($title)
		{
			ini_set("date.timezone", 'America/El_Salvador');
            $date = date("j/m/Y");
			$aux .= "
			<div class='PDF_header'>
				<div class='logo'>
		            <img src='../../img/ezic.png'>
				</div>
				<div class='info'>
					<p class='date'>$date</p>
		            <h4 class='title'>$title</h4><br>
				</div>
			</div>
            ";

            $this->mpdf->WriteHTML($aux, 2);
		}

		function getUser($id)
		{
			$this->stylesheet = file_get_contents('../../mpdf/resources/user.css');
			$auxCSS = file_get_contents('../../mpdf/resources/colors.css');

			$this->_print = $this->administration->printUser($this->administration->get_user_data($id));

			// $this->mpdf = new mPDF('utf-8', 'A4', 0, '', 10, 10, 10, 0, 0, 0, 'P');
			$this->mpdf = new mPDF();
			$this->mpdf->WriteHTML($auxCSS, 1);
			$this->mpdf->setTitle('Ezic: Usuarios.');
			$this->mpdf->setAuthor('Ezic ©');
			$this->openPDF("Perfil de usuario", ($id));
		}


		function openPDF($title, $name)
		{
			$this->mpdf->WriteHTML($this->stylesheet, 1);
			$this->genHeader($title);
			$this->mpdf->WriteHTML($this->_print, 2);
			$this->mpdf->Output($name.".pdf", "I");
			// $this->mpdf->Output($name.".pdf", "D");
			// header('Content-Disposition: attachment; filename="' . $name . '.pdf"');
		}
	}

	$print = new Print_Class();

	if (isset($_POST['printSchedule'])) {
		$print->getSchedule($_POST['type'], $_POST['id']);
	}

	if (isset($_POST['printRecord'])) {
		$print->getRecord($_POST['id']);
	}

	if (isset($_POST['printUser'])) {
		$print->getUser($_POST['id']);
	}
?>
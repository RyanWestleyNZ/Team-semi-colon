<?php
require 'medoo.min.php';
require 'config.php';
$database = new Medoo();

//Checks to see if an action has been specified. If there's an action (all commands from the
//lecturer.js have one) the controller uses a switch statement to choose a method
if (isset($_GET["action"])) {
	switch($_GET["action"]) {
		case 'pageload': //Called when the page loads
			//Uses a Medoo query to fetch all papers from the courses
			$data =$database->query("select * from courses")->fetchAll();
			
			//Write HTML for a table head to a string
			$html = "";
			$html.= "<table>";
			$html.= "<th>Paper ID</th><th>Paper Name</th><th>Pre-Requisite</th><th>Compulsory</th><th>Description</th><th>Semester Available</th><th>Credits</th><th>Edit</th><th>Delete</th>";
			
			//Uses a foreach loop to load each paper into a table row.
			//Each paper loaded has their paper ID connected to the 'Edit' and 'Delete' buttons 
			//via data-id, so that information can be drawn from the database later.
			foreach ($data as $value)
			{
				$html.= "<tr>"
						. "<td>" . $value['Paper_ID'] . "</td>"
						. "<td>" . $value['Paper_Name'] . "</td>"
						. "<td>" . $value['Pre_Requisite'] . "</td>"
						. "<td>" . $value['Compulsory'] . "</td>"
						. "<td>" . $value['Description'] . "</td>"
						. "<td>" . $value['Semester_Available'] . "</td>"
						. "<td>" . $value['Credits'] . "</td>"
						. "<td><a href='#' class='editpaper' data-id='" . $value['Paper_ID'] . "'>Edit</a></td> "
						. "<td><a href='lecturer.php' class='deletepaper' data-id='" . $value['Paper_ID'] . "'>Delete</a></td>"
						. "</tr>";
			}
			$html.="</table>";
			
			//Relay the table HTML to the file that called the controller
			echo $html;
			break;
			
		case 'edit': //Called when the user selects 'Edit' for a paper
			//Uses a Medoo query to find the paper to be edited in the database.
			//The paper ID is fed into the controller from the 'Edit' button so one can query 
			//based off the Paper_ID field
			$data = $database->select("courses", [
				"Paper_ID",
				"Paper_Name",
				"Pre_Requisite",
				"Compulsory",
				"Description",
				"Semester_Available",
				"Credits"
					], [
				"Paper_ID[=]" => $_GET['value']
			]);
			
			//Writes HTML for the editing form to a $form variable. The fields are populated with
			//the current information on the selected paper, and the save button has the paper ID
			//as a data-id for the updating process
			foreach ($data as $value)
			{
				$form = "<form>";
				$form.= "<label>Paper ID:</label><br><input type='text' id='Paper_ID' name='Paper_ID' value='".$value['Paper_ID']."'><br>";
				$form.= "<label>Paper Name:</label><br><input type='text' id='Paper_Name' name='Paper_Name' value='".$value['Paper_Name']."'><br>";
				$form.= "<label>Pre-Requisite:</label><br><input type='text' id='Pre_Requisite' name='Pre_Requisite' value='".$value['Pre_Requisite']."'><br>";
				$form.= "<label>Compulsory:</label><br><input type='text' id='Compulsory' name='Compulsory' value='".$value['Compulsory']."'><br>";
				$form.= "<label>Description:</label><br><input type='text' id='Description' name='Description' value='".$value['Description']."'><br>";
				$form.= "<label>Semester Available:</label><br><input type='text' id='Semester_Available' name='Semester_Available' value='".$value['Semester_Available']."'><br>";
				$form.= "<label>Credits:</label><br><input type='text' id='Credits' name='Credits' value='".$value['Credits']."'><br>";
				$form.= "<button href='#' class='updateexisting' data-id='".$value['Paper_ID']."'>Save</button>";
				$form.= "</form>";
			}
			
			//Relay the form HTML to the file that called the controller
			echo $form;
			break;
			
		case 'new': //Called then the user selects 'Add New Paper'
			//Writes HTML for the editing form to a $form variable. This is almost functionally 
			//identical to the form in the 'edit' case, however the form is blank and the 'save' 
			//button has no data-id
			$form = "<form>";
			$form.= "<label>Paper ID:</label><br><input type='text' id='Paper_ID' name='Paper_ID'><br>";
			$form.= "<label>Paper Name:</label><br><input type='text' id='Paper_Name' name='Paper_Name'><br>";
			$form.= "<label>Pre-Requisite:</label><br><input type='text' id='Pre_Requisite' name='Pre_Requisite'><br>";
			$form.= "<label>Compulsory:</label><br><input type='text' id='Compulsory' name='Compulsory'><br>";
			$form.= "<label>Description:</label><br><input type='text' id='Description' name='Description'><br>";
			$form.= "<label>Semester Available:</label><br><input type='text' id='Semester_Available' name='Semester_Available'><br>";
			$form.= "<label>Credits:</label><br><input type='text' id='Credits' name='Credits'><br>";
			$form.= "<button href='#' class='savenew'>Save</button>";
			$form.= "</form>";
			
			//Relay the form HTML to the file that called the controller
			echo $form;
			break;
			
		case 'delete': //Called when the user selects 'Delete' for a paper
			//The SQL statement is saved to a variable first for the sake of easy reading
			$delete = "DELETE FROM courses WHERE Paper_ID = '". $_GET['value'] ."'";
			//The mysqli_query is contained in an if statement so if the query fails, the error
			//can be echoed out in place of a success message. The same applies to the update and
			//create cases
			if(!mysqli_query($db, $delete)) {
				echo mysqli_error($db);
			} else {
				echo "Successfully deleted paper";
			}
			break;
		
		case 'update': //Called when the user selects 'Save' on an existing paper
			//Writes an update statement to a variable, taking all of its values from the form
			//Uses substrings of the Paper_ID to draw the required information for Paper_Year
			//and Paper_Category (the letter and the first number correspond to it). This also
			//applies in the create case
			$update = "UPDATE courses"
				." SET Paper_ID='". $_POST['Paper_ID']
				."', Paper_Name='". $_POST['Paper_Name']
				."', Pre_Requisite='". $_POST['Pre_Requisite']
				."', Compulsory='". $_POST['Compulsory']
				."', Description='". $_POST['Description']
				."', Semester_Available='". $_POST['Semester_Available']
				."', Credits='". $_POST['Credits']
				."', Paper_Year='". substr($_POST['Paper_ID'], 1, 1)
				."', Paper_Category='". substr($_POST['Paper_ID'], 0, 1)
				."' WHERE Paper_ID='". $_GET['value'] ."';";
			if(!mysqli_query($db, $update)) {
				echo mysqli_error($db);
			} else {
				echo "Successfully updated paper";
			}
		break;
		
		case 'create': //Called when the user selects 'Save' on a new paper
			//Writes a create statement to a variable, taking all of its values from the form
			$create = "INSERT INTO courses"
					." VALUES ('"
					.$_POST['Paper_ID']."', '"
					.$_POST['Paper_Name']."', '"
					.$_POST['Pre_Requisite']."', "
					.$_POST['Compulsory'].", '"
					.$_POST['Description']."', '"
					.$_POST['Semester_Available']."', "
					.$_POST['Credits'].", "
					.substr($_POST['Paper_ID'], 1, 1).", '"
					.substr($_POST['Paper_ID'], 0, 1)."')";
			if(!mysqli_query($db, $create)) {
				echo mysqli_error($db);
			} else {
				echo "Successfully added paper";
			}
		break;
	}
}
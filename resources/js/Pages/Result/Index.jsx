import { Head, Link } from "@inertiajs/react";
import { useEffect, useCallback, useState } from "react";
import { Row, Col, Card, Button, ButtonGroup, Table, Spinner, Dropdown, Modal, Form } from "react-bootstrap";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import axios from 'axios';
import Swal from 'sweetalert2';

export default function ResultsIndex({ exam, classData }) {
    const [subjectConfig, setSubjectConfig] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showImportModal, setShowImportModal] = useState(false);
    const [selectedFile, setSelectedFile] = useState(null);
    const [importProgress, setImportProgress] = useState(0);
    const [isImporting, setIsImporting] = useState(false);
    const [importStatus, setImportStatus] = useState(null);

    // Fetch subjects from API
    useEffect(() => {
        const fetchSubjects = async () => {
            try {
                const response = await axios.get(route('api.subjects'));
                setSubjectConfig(response.data);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching subjects:', error);
                setLoading(false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch subjects. Please try again.',
                });
            }
        };

        fetchSubjects();
    }, []);

    const handleFileChange = (e) => {
        setSelectedFile(e.target.files[0]);
    };

    const handleImport = async () => {
        if (!selectedFile) {
            Swal.fire({
                icon: 'warning',
                title: 'No File Selected',
                text: 'Please select a file first',
            });
            return;
        }

        setIsImporting(true);
        setImportProgress(0);
        setImportStatus(null);

        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('exam_id', exam.exam_key);
        formData.append('class_id', classData);

        try {
            const response = await axios.post(route('results.import'), formData, {
                onUploadProgress: (progressEvent) => {
                    const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    setImportProgress(progress);
                },
            });

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.data.message || "Results imported successfully!",
            });

            // Refresh the table after successful import
            if ($.fn.DataTable.isDataTable("#resultsTable")) {
                $("#resultsTable").DataTable().ajax.reload();
            }

            setTimeout(() => {
                setShowImportModal(false);
                setIsImporting(false);
            }, 2000);
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || "Import failed";
            Swal.fire({
                icon: 'error',
                title: 'Import Failed',
                text: errorMessage,
            });
            setIsImporting(false);
        }
    };

    const initializeDataTable = useCallback(() => {
        if (!subjectConfig.length || loading) return;

        if ($.fn.DataTable.isDataTable("#resultsTable")) {
            $("#resultsTable").DataTable().destroy();
        }

        // Generate subject columns dynamically
        const generatedSubjectColumns = subjectConfig.map(subject => ({
            data: `scores.${subject.code}`,
            title: subject.short_name || subject.name,
            render: function (data) { return data || '--'; },
            className: "text-nowrap"
        }));

        // Base columns (non-subject)
        const baseColumns = [
            { data: "adm", title: "Adm", orderable: false },
            { data: "name", title: "Name", className: "text-nowrap" },
            { data: "class", title: "Class", className: "text-capitalize text-nowrap" },
            { data: "kcpe", title: "KCPE" }
        ];

        // Result columns (after subjects)
        const resultColumns = [
            { data: "re_subC", title: "Count" },
            { data: "re_tt", title: "Marks", className: "fw-bold text-danger" },
            { data: "re_mean", title: "Mean", className: "fw-bold text-primary" },
            { data: "re_pnt", title: "Points", className: "fw-bold text-danger" },
            { data: "re_avgpnt", title: "Average", className: "fw-bold text-primary" },
            { data: "re_grade", title: "Grade", className: "fw-bold text-success" },
            { data: "re_sRank", title: "Rank", className: "fw-bold text-dark" },
            { data: "re_fRank", title: "Position", className: "fw-bold text-dark" },
        ];

        // Combine all columns
        const allColumns = [...baseColumns, ...generatedSubjectColumns, ...resultColumns];

        $("#resultsTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: route("results.index", { exam: exam.id, class: classData }),
                type: "GET",
            },
            columns: allColumns,
            language: {
                emptyTable: "No results available",
                processing: "Loading results...",
            },
            createdRow: function (row, data, index) {
                // Highlight dropped subjects if needed
                $('td', row).each(function () {
                    if ($(this).text() === '--') {
                        $(this).addClass('text-muted');
                    }
                });
            }
        });
    }, [exam.id, classData, subjectConfig, loading]);

    useEffect(() => {
        initializeDataTable();

        return () => {
            if ($.fn.DataTable.isDataTable("#resultsTable")) {
                $("#resultsTable").DataTable().destroy();
            }
        };
    }, [initializeDataTable]);

    const confirmImport = () => {
        Swal.fire({
            title: 'Import Results?',
            text: "Are you sure you want to import these results? This action cannot be undone.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, import!'
        }).then((result) => {
            if (result.isConfirmed) {
                handleImport();
            }
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Results List" />

            {/* Import Results Modal */}
            <Modal show={showImportModal} onHide={() => setShowImportModal(false)}>
                <Modal.Header closeButton>
                    <Modal.Title>Import Results from Excel</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form>
                        <Form.Group controlId="formFile" className="mb-3">
                            <Form.Label>Select Excel File</Form.Label>
                            <Form.Control
                                type="file"
                                accept=".xlsx, .xls, .csv"
                                onChange={handleFileChange}
                                disabled={isImporting}
                            />
                            <Form.Text className="text-muted">
                                Please upload an Excel file with the correct format.
                                <a href="/templates/results-template.xlsx" download className="ms-1">Download template</a>
                            </Form.Text>
                        </Form.Group>

                        {isImporting && (
                            <div className="mb-3">
                                <div className="d-flex justify-content-between mb-1">
                                    <span>Importing...</span>
                                    <span>{importProgress}%</span>
                                </div>
                                <div className="progress">
                                    <div
                                        className="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar"
                                        style={{ width: `${importProgress}%` }}
                                    ></div>
                                </div>
                            </div>
                        )}
                    </Form>
                </Modal.Body>
                <Modal.Footer>
                    <Button
                        variant="secondary"
                        onClick={() => setShowImportModal(false)}
                        disabled={isImporting}
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="primary"
                        onClick={confirmImport}
                        disabled={isImporting || !selectedFile}
                    >
                        {isImporting ? (
                            <>
                                <Spinner as="span" animation="border" size="sm" role="status" aria-hidden="true" />
                                <span className="ms-2">Importing...</span>
                            </>
                        ) : (
                            'Import Results'
                        )}
                    </Button>
                </Modal.Footer>
            </Modal>

            <Row className="mb-3 align-items-center">
                <Col>
                    <h1 className="h3 mb-0">Form {classData} Results</h1>
                    <p className="text-muted mb-0">Term: {exam.term.term} Exam: {exam.exam}</p>
                </Col>
                <Col className="d-flex justify-content-end">
                    <Button className="me-2" onClick={() => setShowImportModal(true)}>
                        <i className="bi bi-upload me-2"></i>
                        Upload Excel
                    </Button>
                    <Dropdown align="end">
                        <Dropdown.Toggle
                            variant="outline-success"
                            id="dropdown-downloads"
                            className="d-flex align-items-center gap-2"
                        >
                            <i className="bi bi-download me-1"></i>
                            Downloads
                        </Dropdown.Toggle>
                        <Dropdown.Menu className="border-0 shadow-sm">
                            <Dropdown.Item href={route('pdf.merit', [exam.id, classData])} target="_blank" className="d-flex align-items-center gap-2">
                                <i className="bi bi-file-earmark-pdf text-danger"></i>
                                Merit List - PDF
                            </Dropdown.Item>
                            <Dropdown.Item href={route('pdf.analysis', [exam.id, classData])} target="_blank" className="d-flex align-items-center gap-2">
                                <i className="bi bi-file-earmark-pdf text-danger"></i>
                                Analysis - PDF
                            </Dropdown.Item>
                            <Dropdown.Item href={route('pdf.report-form', [exam.id, classData])} target="_blank" className="d-flex align-items-center gap-2">
                                <i className="bi bi-file-earmark-pdf text-danger"></i>
                                Report Form
                            </Dropdown.Item>
                        </Dropdown.Menu>
                    </Dropdown>
                </Col>
            </Row>
            <hr className="dashed-hr mb-4" />
            <Row>
                <Col xs={12}>
                    <Card className="border-0 shadow-sm">
                        <Card.Body>
                            <Table
                                bordered
                                striped
                                hover
                                responsive
                                id="resultsTable"
                                className="w-100 mb-0"
                            />
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
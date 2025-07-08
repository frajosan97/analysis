import { Head, Link } from "@inertiajs/react";
import { useEffect, useCallback, useState } from "react";
import { Row, Col, Card, ButtonGroup, Table, Spinner, Dropdown } from "react-bootstrap";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function ResultsIndex({ exam, classData }) {
    const [subjectConfig, setSubjectConfig] = useState([]);
    const [loading, setLoading] = useState(true);

    // Fetch subjects from API
    useEffect(() => {
        const fetchSubjects = async () => {
            try {
                const response = await fetch(route('api.subjects'));
                const data = await response.json();
                setSubjectConfig(data);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching subjects:', error);
                setLoading(false);
            }
        };

        fetchSubjects();
    }, []);

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

    return (
        <AuthenticatedLayout>
            <Head title="Results List" />

            <Row className="mb-3 align-items-center">
                <Col>
                    <h1 className="h3 mb-0">Form {classData} Results</h1>
                    <p className="text-muted mb-0">Term: {exam.term.term} Exam: {exam.exam}</p>
                </Col>
                <Col className="d-flex justify-content-end">
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
                                Pdf
                            </Dropdown.Item>
                            <Dropdown.Item href="#/action-2" className="d-flex align-items-center gap-2">
                                <i className="bi bi-file-earmark-excel text-success"></i>
                                Excel
                            </Dropdown.Item>
                            <Dropdown.Item href="#/action-3" className="d-flex align-items-center gap-2">
                                <i className="bi bi-file-earmark-text"></i>
                                Report Forms
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
import { Head, Link } from "@inertiajs/react";
import { useEffect, useCallback } from "react";
import { Row, Col, Card, ButtonGroup, Table } from "react-bootstrap";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function ExamsIndex() {
    const initializeDataTable = useCallback(() => {
        if ($.fn.DataTable.isDataTable("#examsTable")) {
            $("#examsTable").DataTable().destroy();
        }

        $("#examsTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: route("exams.index"),
                type: "GET",
            },
            columns: [
                {
                    data: "exam",
                    title: "Name",
                },
                {
                    data: "term",
                    title: "Term",
                },
                {
                    data: "start_date",
                    title: "Start Date",
                },
                {
                    data: "end_date",
                    title: "End Date",
                },
                {
                    data: "status_badge",
                    title: "Status",
                },
                {
                    data: "action",
                    title: "Action",
                    orderable: false,
                    searchable: false,
                },
            ],
            order: [[0, "desc"]],
            language: {
                emptyTable: "No exams available",
                processing: "Loading exams...",
            }
        });
    }, []);

    useEffect(() => {
        initializeDataTable();

        return () => {
            if ($.fn.DataTable.isDataTable("#examsTable")) {
                $("#examsTable").DataTable().destroy();
            }
        };
    }, [initializeDataTable]);

    return (
        <AuthenticatedLayout>
            <Head title="Exams List" />

            <Row className="mb-3 align-items-center">
                <Col>
                    <h1 className="h3 mb-0">Exams Management</h1>
                </Col>
                <Col xs="auto">
                    <ButtonGroup>
                        <Link
                            href={route("exams.create")}
                            className="btn btn-primary"
                        >
                            Create New Exam
                        </Link>
                    </ButtonGroup>
                </Col>
            </Row>

            <hr className="dashed-hr mb-4" />

            <Row>
                <Col xs={12}>
                    <Card className="border-0 shadow-sm">
                        <Card.Body className="p-0">
                            <Table
                                bordered
                                striped
                                hover
                                responsive
                                id="examsTable"
                                className="w-100 mb-0"
                            />
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
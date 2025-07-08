import { Head, Link } from "@inertiajs/react";
import { Row, Col, Card, ButtonGroup, Table } from "react-bootstrap";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function ExamsShow({ exam, classData }) {
    const classes = [2, 3, 4];

    return (
        <AuthenticatedLayout>
            <Head title={`Exam Details - ${exam.exam}`} />

            <Row className="mb-3 align-items-center">
                <Col>
                    <h1 className="h3 mb-0">Exam: {exam.exam}</h1>
                    <p className="text-muted mb-0">Term: {exam.term.term}</p>
                </Col>
                <Col xs="auto">
                    <ButtonGroup>
                        <Link
                            href={route("exams.index")}
                            className="btn btn-outline-secondary"
                        >
                            Back to Exams
                        </Link>
                    </ButtonGroup>
                </Col>
            </Row>

            <hr className="dashed-hr mb-4" />

            <Row className="mb-4">
                <Col md={6}>
                    <Card className="mb-3">
                        <Card.Body>
                            <h5 className="card-title">Exam Details</h5>
                            <dl className="row mb-0">
                                <dt className="col-sm-4">Start Date</dt>
                                <dd className="col-sm-8">{exam.start_date}</dd>

                                <dt className="col-sm-4">End Date</dt>
                                <dd className="col-sm-8">{exam.end_date}</dd>

                                <dt className="col-sm-4">Status</dt>
                                <dd className="col-sm-8">
                                    <span className={`badge ${new Date(exam.end_date) > new Date() ?
                                        'bg-success' : 'bg-secondary'}`}>
                                        {new Date(exam.end_date) > new Date() ? 'Active' : 'Completed'}
                                    </span>
                                </dd>
                            </dl>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>

            <Row>
                <Col xs={12}>
                    <Card className="border-0 shadow-sm">
                        <Card.Body className="p-0">
                            <Table bordered hover responsive className="mb-0">
                                <thead className="bg-light">
                                    <tr>
                                        <th>Class</th>
                                        <th>Students Count</th>
                                        <th>Average Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {classes.map(classNumber => {
                                        const data = classData[classNumber] || {};
                                        const topStudent = data.top_student;
                                        return (
                                            <tr key={classNumber}>
                                                <td>Class {classNumber}</td>
                                                <td>{data.student_count || 0}</td>
                                                <td>{data.average_score ? `${data.average_score}%` : 'N/A'}</td>
                                                <td>
                                                    <ButtonGroup>
                                                        <Link
                                                            href={route('results.index', {
                                                                exam: exam.id,
                                                                class: classNumber
                                                            })}
                                                            className="btn btn-sm btn-primary"
                                                        >
                                                            View Results
                                                        </Link>
                                                    </ButtonGroup>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </Table>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </AuthenticatedLayout>
    );
}
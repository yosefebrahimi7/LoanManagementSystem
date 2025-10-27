<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *   title="Loan Management System API",
 *   version="1.0.0",
 *   description="API documentation for Loan Management System"
 * )
 * 
 * @OA\Server(
 *   url="http://localhost:8000",
 *   description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="email", type="string", example="user@example.com"),
 *   @OA\Property(property="firstName", type="string", example="John"),
 *   @OA\Property(property="lastName", type="string", example="Doe"),
 *   @OA\Property(property="isActive", type="boolean", example=true),
 *   @OA\Property(property="role", type="integer", example=1),
 *   @OA\Property(property="roleName", type="string", example="user"),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *   schema="Loan",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="userId", type="integer", example=1),
 *   @OA\Property(property="amount", type="integer", example=10000000),
 *   @OA\Property(property="termMonths", type="integer", example=12),
 *   @OA\Property(property="interestRate", type="number", format="float", example=14.5),
 *   @OA\Property(property="monthlyPayment", type="integer", example=850000),
 *   @OA\Property(property="remainingBalance", type="integer", example=10000000),
 *   @OA\Property(property="status", type="string", example="pending"),
 *   @OA\Property(property="startDate", type="string", format="date", example="2025-11-01"),
 *   @OA\Property(property="approvedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="approvedBy", type="integer", nullable=true),
 *   @OA\Property(property="rejectionReason", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *   schema="Notification",
 *   type="object",
 *   @OA\Property(property="id", type="string", example="uuid"),
 *   @OA\Property(property="type", type="string", example="App\\Notifications\\NewLoanRequestNotification"),
 *   @OA\Property(property="data", type="object", example={"message": "درخواست وام جدید ثبت شد", "type": "new_loan_request", "loan_id": 1}),
 *   @OA\Property(property="readAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 */
class SwaggerController extends Controller
{
    //
}


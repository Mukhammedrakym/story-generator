from typing import Optional
from fastapi import Request
from fastapi.responses import JSONResponse
from starlette import status

class AppError(Exception):
    status_code: int = status.HTTP_500_INTERNAL_SERVER_ERROR
    message: str = "Internal Server Error"

    def __init__(self, message: Optional[str] = None, *, status_code: Optional[int] = None):
        if message:
            self.message = message
        if status_code is not None:
            self.status_code = status_code
        super().__init__(self.message)

class DomainValidationError(AppError):
    status_code = status.HTTP_422_UNPROCESSABLE_ENTITY

class UpstreamError(AppError):
    status_code = status.HTTP_502_BAD_GATEWAY

# Добавьте эти функции:
async def app_error_handler(_req: Request, exc: AppError):
    return JSONResponse(
        status_code=exc.status_code,
        content={"error": exc.__class__.__name__, "message": exc.message},
    )

async def unhandled_error_handler(_req: Request, exc: Exception):
    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={"error": "InternalServerError", "message": str(exc)},
    )